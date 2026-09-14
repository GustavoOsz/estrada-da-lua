(() => {
  document.querySelectorAll('[data-preview]').forEach(input => {
    const out = document.getElementById(input.dataset.preview);
    if (!out) return;
    input.addEventListener('input', () => out.textContent = input.value || '—');
  });
  const map = {type:'editorialType', title:'editorialTitle', summary:'editorialSummary'};
  Object.entries(map).forEach(([key,id]) => {
    const input = document.querySelector(`[data-editorial="${key}"]`), out=document.getElementById(id);
    if(input&&out) input.addEventListener('input',()=>out.textContent=input.value || (key==='title'?'Seu título começa aqui.':key==='summary'?'O resumo aparece enquanto você escreve.':'Blog'));
  });
  document.querySelectorAll('[data-image-preview]').forEach(input => input.addEventListener('change',()=>{
    const box=document.getElementById(input.dataset.imagePreview);const file=input.files?.[0];if(!box||!file)return;const reader=new FileReader();reader.onload=e=>box.innerHTML=`<img src="${e.target.result}" alt="Prévia">`;reader.readAsDataURL(file);
  }));
  const bindProductPreview = () => {
    const name=document.querySelector('[name="nome"]'), desc=document.querySelector('[name="descricao"]'), price=document.querySelector('[name="preco"]'), stock=document.querySelector('[name="estoque"]'), ready=document.querySelector('[name="pronta_entrega"]');
    const set=(id,val)=>{const el=document.getElementById(id);if(el)el.textContent=val};
    const update=()=>{if(name)set('pvName',name.value||'Nome da peça');if(desc)set('pvDesc',desc.value||'A descrição da peça aparece aqui.');if(price){let v=parseFloat((price.value||'0').replace(',','.'));set('pvPrice',v.toLocaleString('pt-BR',{style:'currency',currency:'BRL'}));}if(stock)set('pvStock',`${stock.value||0} em estoque`);const badge=document.getElementById('pvReady');if(badge)badge.style.display=ready?.checked?'inline-block':'none';};[name,desc,price,stock,ready].filter(Boolean).forEach(el=>el.addEventListener('input',update));update();
  };bindProductPreview();
})();
