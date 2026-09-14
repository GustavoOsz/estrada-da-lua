<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/schema.php';
ensureFullSchema($pdo);
if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') { http_response_code(403); exit('Apenas administradores podem exportar contatos.'); }
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="estrada-da-lua-contatos-autorizados.csv"');
$out=fopen('php://output','w');
fwrite($out, "\xEF\xBB\xBF");
fputcsv($out,['Nome','E-mail','Telefone','Interesses','Consentimento em'],';');
$st=$pdo->query("SELECT nome,email,telefone,interesses,marketing_consentido_em FROM clientes WHERE aceita_marketing=1 ORDER BY nome");
foreach($st as $c)fputcsv($out,[$c['nome'],$c['email'],$c['telefone'],$c['interesses'],$c['marketing_consentido_em']],';');
fclose($out);exit;
