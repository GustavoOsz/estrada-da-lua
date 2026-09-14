(() => {
  const money = v => Number(v || 0).toLocaleString('pt-BR',{style:'currency',currency:'BRL'});
  const stack=document.getElementById('toastStack');
  const toast=(message,type='success')=>{
    if(!stack||!message)return;const el=document.createElement('div');el.className=`ui-toast ${type==='error'?'error':'success'}`;const mark=document.createElement('span');mark.textContent=type==='error'?'!':'✓';const body=document.createElement('div');body.textContent=message;const closeBtn=document.createElement('button');closeBtn.type='button';closeBtn.textContent='×';el.append(mark,body,closeBtn);stack.appendChild(el);requestAnimationFrame(()=>el.classList.add('show'));const close=()=>{el.classList.remove('show');setTimeout(()=>el.remove(),240)};closeBtn.addEventListener('click',close);setTimeout(close,5000);
  };
  window.AdminToast=toast;
  document.querySelectorAll('.server-flashes [data-flash-type]').forEach(el=>toast(el.textContent.trim(),el.dataset.flashType));
  document.querySelectorAll('.alert').forEach(el=>{toast(el.textContent.trim(),el.classList.contains('alert-error')?'error':'success');el.classList.add('alert-enhanced');});

  // Modal de confirmação para formulários e links administrativos.
  const modal=document.getElementById('adminConfirmModal');let pending=null;
  const close=()=>{if(!modal)return;modal.classList.remove('open');modal.setAttribute('aria-hidden','true');pending=null;};
  document.querySelectorAll('[data-confirm]').forEach(el=>{
    el.addEventListener(el.tagName==='FORM'?'submit':'click',e=>{if(el.dataset.confirmed==='1'){delete el.dataset.confirmed;return;}e.preventDefault();pending=el;document.getElementById('adminConfirmText').textContent=el.dataset.confirm||'Confirmar esta ação?';modal.classList.add('open');modal.setAttribute('aria-hidden','false');});
  });
  modal?.querySelectorAll('[data-admin-confirm-cancel]').forEach(b=>b.addEventListener('click',close));
  modal?.querySelector('[data-admin-confirm-ok]')?.addEventListener('click',()=>{const t=pending;if(!t)return;t.dataset.confirmed='1';close();if(t.tagName==='FORM')t.requestSubmit();else location.href=t.href;});

  // Prévia editorial mais completa.
  const editorial = {
    type: document.querySelector('[data-editorial="type"]'),
    category: document.querySelector('[data-editorial="category"]'),
    time: document.querySelector('[data-editorial="time"]'),
    title: document.querySelector('[data-editorial="title"]'),
    summary: document.querySelector('[data-editorial="summary"]'),
    body: document.querySelector('[data-editorial="body"]'),
    cta: document.querySelector('[data-editorial="cta"]')
  };
  const set=(id,val)=>{const el=document.getElementById(id);if(el)el.textContent=val;};
  const updateEditorial=()=>{
    if(editorial.type)set('editorialType',editorial.type.value||'Blog');
    if(editorial.category)set('editorialCategory',editorial.category.value||'Estrada da Lua');
    if(editorial.time)set('editorialTime',editorial.time.value?`${editorial.time.value} min`:'');
    if(editorial.title)set('editorialTitle',editorial.title.value||'Seu título começa aqui.');
    if(editorial.summary)set('editorialSummary',editorial.summary.value||'O resumo aparece enquanto você escreve.');
    if(editorial.body){set('editorialBody',(editorial.body.value||'Um trecho do conteúdo aparece aqui para você sentir ritmo, respiro e hierarquia antes de publicar.').slice(0,260));document.querySelector('[data-editorial-count]')?.replaceChildren(document.createTextNode(String(editorial.body.value.length)));}
    if(editorial.cta)set('editorialCta',editorial.cta.value||'Continuar a experiência →');
  };
  Object.values(editorial).filter(Boolean).forEach(el=>el.addEventListener('input',updateEditorial));updateEditorial();

  document.querySelectorAll('[data-preview-device]').forEach(btn=>btn.addEventListener('click',()=>{document.querySelectorAll('[data-preview-device]').forEach(b=>b.classList.remove('active'));btn.classList.add('active');document.getElementById('homeLivePreview')?.classList.toggle('mobile-preview',btn.dataset.previewDevice==='mobile');}));

  // Registro de venda sem sair da tela.
  const saleModal=document.getElementById('saleModal');
  const openSale=()=>{saleModal?.classList.add('open');saleModal?.setAttribute('aria-hidden','false');document.body.classList.add('modal-open');};
  const closeSale=()=>{saleModal?.classList.remove('open');saleModal?.setAttribute('aria-hidden','true');document.body.classList.remove('modal-open');};
  document.querySelector('[data-open-sale-modal]')?.addEventListener('click',openSale);
  saleModal?.querySelectorAll('[data-close-sale]').forEach(b=>b.addEventListener('click',closeSale));
  const saleForm=document.querySelector('[data-sale-form]');
  saleForm?.addEventListener('submit',async e=>{
    e.preventDefault();const submit=saleForm.querySelector('[type="submit"]');submit.disabled=true;submit.textContent='Salvando…';
    try{
      const res=await fetch(saleForm.action,{method:'POST',body:new FormData(saleForm),headers:{'X-Requested-With':'XMLHttpRequest'}});const data=await res.json();if(!res.ok||!data.ok)throw new Error(data.message||'Não foi possível salvar a venda.');
      toast(data.message||'Venda registrada.');
      if(data.month===saleForm.dataset.currentMonth){
        const s=data.summary;set('metricProfit',money(s.lucro));set('metricMargin',`${Number(s.margem).toLocaleString('pt-BR',{maximumFractionDigits:1})}%`);set('metricTicket',money(s.ticket));set('metricCount',s.count);set('metricRevenue',money(s.receita));set('metricCosts',money(s.custos));set('metricPositive',`${s.positivas}/${s.count}`);set('metricNegative',`${s.negativas} venda(s) no vermelho`);set('metricHealth',s.lucro>=0?'Fechando no verde':'Fechando no vermelho');const hero=document.getElementById('financeHero');hero?.classList.toggle('green',s.lucro>=0);hero?.classList.toggle('red',s.lucro<0);
        document.getElementById('financeEmpty')?.remove();
        const v=data.sale;const mg=Number(v.margem).toLocaleString('pt-BR',{maximumFractionDigits:1});const [y,m,d]=v.data.split('-');const tr=document.createElement('tr');tr.className=v.lucro>=0?'row-green':'row-red';tr.innerHTML=`<td>${d}/${m}</td><td></td><td><strong></strong><small></small></td><td>${money(v.receita)}</td><td>${money(v.custo)}</td><td><strong>${money(v.lucro)}</strong></td><td>${mg}%</td><td><a class="table-link" href="${document.body.dataset.baseUrl}/admin/venda.php?id=${v.id}">Editar</a></td>`;tr.children[1].textContent=v.origem;tr.children[2].querySelector('strong').textContent=v.descricao;tr.children[2].querySelector('small').textContent=v.cliente||'';document.getElementById('financeTableBody')?.prepend(tr);
      } else toast('A venda foi salva no mês correspondente à data informada.');
      saleForm.reset();saleForm.querySelector('[name="data_venda"]').value=new Date().toISOString().slice(0,10);closeSale();
    }catch(err){toast(err.message,'error');}finally{submit.disabled=false;submit.textContent='Salvar venda';}
  });


  // Editor de páginas: prévia real no iframe e atualização ao vivo dos trechos marcados.
  const pagePreviewFrame = document.getElementById('pagePreviewFrame');
  const pageEditorFields = Array.from(document.querySelectorAll('[data-content-editor-key]'));
  const updatePagePreview = () => {
    if (!pagePreviewFrame?.contentDocument) return;
    pageEditorFields.forEach((field) => {
      const key = field.dataset.contentEditorKey;
      if (!key) return;
      const targets = pagePreviewFrame.contentDocument.querySelectorAll(`[data-content-key="${CSS.escape(key)}"]`);
      targets.forEach((target) => {
        target.textContent = field.value;
        if (field.value.includes('\n')) target.style.whiteSpace = 'pre-line';
      });
    });
  };
  pagePreviewFrame?.addEventListener('load', () => setTimeout(updatePagePreview, 100));
  pageEditorFields.forEach((field) => field.addEventListener('input', updatePagePreview));
  document.querySelector('[data-refresh-page-preview]')?.addEventListener('click', () => {
    if (pagePreviewFrame) pagePreviewFrame.src = pagePreviewFrame.src;
  });
})();
