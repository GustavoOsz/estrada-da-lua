<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/schema.php';
require_once __DIR__ . '/../includes/functions.php';
ensureFullSchema($pdo);
header('Content-Type: application/json; charset=utf-8');
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);echo json_encode(['ok'=>false,'message'=>'Método não permitido.']);exit;}
try{
    $origem=trim($_POST['origem']??'Outro');$cliente=trim($_POST['cliente']??'');$descricao=trim($_POST['descricao']??'');$receita=parseMoneyInput($_POST['receita']??0);$cm=parseMoneyInput($_POST['custo_materiais']??0);$ct=parseMoneyInput($_POST['custo_tempo']??0);$ce=parseMoneyInput($_POST['custo_envio']??0);$co=parseMoneyInput($_POST['outros_custos']??0);$data=$_POST['data_venda']??date('Y-m-d');$obs=trim($_POST['observacoes']??'');
    if($descricao===''||$receita<0||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$data)) throw new RuntimeException('Revise descrição, receita e data.');
    $s=$pdo->prepare("INSERT INTO vendas (origem,cliente,descricao,receita,custo_materiais,custo_tempo,custo_envio,outros_custos,data_venda,observacoes) VALUES (?,?,?,?,?,?,?,?,?,?)");$s->execute([$origem,$cliente,$descricao,$receita,$cm,$ct,$ce,$co,$data,$obs]);$id=(int)$pdo->lastInsertId();
    $mes=substr($data,0,7);$inicio=$mes.'-01';$fim=date('Y-m-d',strtotime($inicio.' +1 month'));
    $s=$pdo->prepare("SELECT * FROM vendas WHERE data_venda>=? AND data_venda<?");$s->execute([$inicio,$fim]);$rows=$s->fetchAll();$r=0;$c=0;$pos=0;$neg=0;foreach($rows as $v){$cv=(float)$v['custo_materiais']+(float)$v['custo_tempo']+(float)$v['custo_envio']+(float)$v['outros_custos'];$lv=(float)$v['receita']-$cv;$r+=(float)$v['receita'];$c+=$cv;$lv>=0?$pos++:$neg++;}$l=$r-$c;$count=count($rows);$m=$r>0?$l/$r*100:0;$ticket=$count?$r/$count:0;$custoVenda=$cm+$ct+$ce+$co;$lucroVenda=$receita-$custoVenda;$margemVenda=$receita>0?$lucroVenda/$receita*100:0;
    echo json_encode(['ok'=>true,'message'=>'Venda registrada com sucesso.','month'=>$mes,'sale'=>['id'=>$id,'origem'=>$origem,'cliente'=>$cliente,'descricao'=>$descricao,'receita'=>$receita,'custo'=>$custoVenda,'lucro'=>$lucroVenda,'margem'=>$margemVenda,'data'=>$data],'summary'=>['receita'=>$r,'custos'=>$c,'lucro'=>$l,'margem'=>$m,'ticket'=>$ticket,'count'=>$count,'positivas'=>$pos,'negativas'=>$neg]],JSON_UNESCAPED_UNICODE);exit;
}catch(Throwable $e){http_response_code(422);echo json_encode(['ok'=>false,'message'=>$e->getMessage()],JSON_UNESCAPED_UNICODE);}
