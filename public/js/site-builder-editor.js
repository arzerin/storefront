(() => {
  const root = document.getElementById('site-builder-form');
  if (!root) return;
  const initial = JSON.parse(document.getElementById('builder-data').textContent);
  let sections = Array.isArray(initial.sections) ? initial.sections : [];
  let selected = sections[0]?.id ?? null;
  const list = document.getElementById('section-list');
  const fields = document.getElementById('section-fields');
  const title = document.getElementById('section-editor-title');
  const labels = {hero:'Hero',benefits:'Benefits strip',image_text:'Image + text',collections:'Collections',products:'Products',faq:'FAQs',free_text:'Free Text',header_text:'Header with text'};
  const defaults = {
    hero:{eyebrow:'',headline:'Your headline',body:'',button_label:'',button_url:''},
    benefits:{items:['Quality pieces','Easy checkout']},
    image_text:{eyebrow:'',headline:'Your story',body:'',button_label:'',button_url:''},
    collections:{eyebrow:'',headline:'Explore collections',collection_ids:[]},
    products:{eyebrow:'',headline:'Explore products',product_ids:[]},
    faq:{headline:'Frequently asked questions',items:[{question:'Your question',answer:'Your answer'}]},
    free_text:{body:'Add your text here.'},
    header_text:{headline:'Your heading',body:'Add your supporting text here.'}
  };
  function newSectionId() {
    const bytes = new Uint8Array(16);
    if (window.crypto?.getRandomValues) window.crypto.getRandomValues(bytes);
    else for (let i = 0; i < bytes.length; i++) bytes[i] = Math.floor(Math.random() * 256);
    bytes[6] = (bytes[6] & 0x0f) | 0x40;
    bytes[8] = (bytes[8] & 0x3f) | 0x80;
    const hex = Array.from(bytes, byte => byte.toString(16).padStart(2, '0')).join('');
    return `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`;
  }
  const active = () => sections.find(section => section.id === selected);
  const button = (text, className = 'btn btn-sm btn-outline-secondary') => {const node=document.createElement('button');node.type='button';node.className=className;node.textContent=text;return node};
  const wrap = (labelText, control) => {const outer=document.createElement('div');outer.className='mb-3';const label=document.createElement('label');label.className='form-label small fw-semibold';label.textContent=labelText;outer.append(label,control);fields.append(outer);return outer};
  function input(label,key,multiline=false) {
    const section=active();const control=document.createElement(multiline?'textarea':'input');
    control.className='form-control form-control-sm';if(multiline) control.rows=4;
    control.value=section.data[key]??'';
    control.addEventListener('input',()=>{section.data[key]=control.value;if(section.type==='hero')section.data.source_settings=false;markDirty()});wrap(label,control);
  }
  function markDirty(){const badge=document.getElementById('builder-dirty');if(badge)badge.textContent='Unsaved changes'}
  function mediaField(){const section=active();const select=document.createElement('select');select.className='form-select form-select-sm';const empty=document.createElement('option');empty.value='';empty.textContent='No library image selected';select.append(empty);for(const asset of initial.media){const option=document.createElement('option');option.value=asset.id;option.textContent=asset.alt+' · '+asset.path.split('/').pop();select.append(option)}select.value=section.data.media_asset_id??'';const outer=wrap('Image from media library',select);const preview=document.createElement('img');preview.style='width:100%;max-height:165px;object-fit:cover;border-radius:6px;margin-top:8px';const updatePreview=()=>{const asset=initial.media.find(m=>String(m.id)===select.value);preview.src=asset?.path??section.data.image_path??'';preview.alt=asset?.alt??section.data.image_alt??'';preview.hidden=!preview.src};select.addEventListener('change',()=>{section.data.media_asset_id=select.value?Number(select.value):null;if(section.type==='hero')section.data.source_settings=false;markDirty();updatePreview()});outer.append(preview);updatePreview();const hint=document.createElement('div');hint.className='form-text';hint.textContent='Upload in Media library, then reopen this editor to select it.';outer.append(hint)}
  function linesField(label,key,parse){const section=active();const control=document.createElement('textarea');control.className='form-control form-control-sm';control.rows=5;control.value=parse=== 'faq'?(section.data[key]??[]).map(item=>`${item.question} | ${item.answer}`).join('\n'):(key==='items'&&section.type==='benefits'&&section.data.source_blocks?initial.benefitBlocks:(section.data[key]??[])).join('\n');control.addEventListener('input',()=>{const lines=control.value.split('\n').map(s=>s.trim()).filter(Boolean);if(key==='items'&&section.type==='benefits')section.data.source_blocks=false;section.data[key]=parse==='faq'?lines.map(line=>{const [question,...answer]=line.split('|');return{question:question.trim(),answer:answer.join('|').trim()}}):lines;markDirty()});const outer=wrap(label,control);const hint=document.createElement('div');hint.className='form-text';hint.textContent=parse==='faq'?'One per line: Question | Answer':'One item per line';outer.append(hint)}
  function catalogField(label,key,items){const section=active();const select=document.createElement('select');select.className='form-select form-select-sm';select.multiple=true;select.size=Math.min(6,Math.max(3,items.length));for(const item of items){const option=document.createElement('option');option.value=item.id;option.textContent=item.name;option.selected=(section.data[key]??[]).map(Number).includes(Number(item.id));select.append(option)}select.addEventListener('change',()=>{section.data[key]=Array.from(select.selectedOptions).map(o=>Number(o.value));markDirty()});const outer=wrap(label,select);const hint=document.createElement('div');hint.className='form-text';hint.textContent='Leave empty to show the latest published items. Hold Command or Ctrl to select several.';outer.append(hint)}
  function renderFields(){fields.replaceChildren();const section=active();title.textContent=section?`${labels[section.type]} settings`:'Section settings';if(!section){fields.textContent='Add a section to begin.';return}if(section.type==='hero'||section.type==='image_text'){input('Eyebrow', 'eyebrow');input('Headline','headline');input('Body text','body',true);mediaField();input('Button label','button_label');input('Button URL','button_url')}else if(section.type==='benefits'){linesField('Benefits','items')}else if(section.type==='collections'){input('Eyebrow','eyebrow');input('Headline','headline');catalogField('Collections','collection_ids',initial.collections)}else if(section.type==='products'){input('Eyebrow','eyebrow');input('Headline','headline');catalogField('Products','product_ids',initial.products)}else if(section.type==='faq'){input('Headline','headline');linesField('Questions and answers','items','faq')}else if(section.type==='free_text'){input('Text','body',true)}else if(section.type==='header_text'){input('Header','headline');input('Text','body',true)}}
  function renderList(){list.replaceChildren();sections.forEach((section,index)=>{const row=button(`${String(index+1).padStart(2,'0')}  ${labels[section.type]}`,'btn btn-sm text-start '+(section.id===selected?'btn-primary':'btn-outline-secondary'));row.addEventListener('click',()=>{selected=section.id;render()});list.append(row)})}
  function render(){renderList();renderFields()}
  document.getElementById('add-section').addEventListener('click',()=>{const type=document.getElementById('add-section-type').value;const section={id:newSectionId(),type,data:JSON.parse(JSON.stringify(defaults[type]))};sections.push(section);selected=section.id;markDirty();render()});
  document.getElementById('move-up').addEventListener('click',()=>{const index=sections.findIndex(s=>s.id===selected);if(index>0){[sections[index-1],sections[index]]=[sections[index],sections[index-1]];markDirty();renderList()}});
  document.getElementById('move-down').addEventListener('click',()=>{const index=sections.findIndex(s=>s.id===selected);if(index>=0&&index<sections.length-1){[sections[index+1],sections[index]]=[sections[index],sections[index+1]];markDirty();renderList()}});
  document.getElementById('remove-section').addEventListener('click',()=>{if(!active()||!confirm(`Remove the ${labels[active().type]} section?`))return;sections=sections.filter(s=>s.id!==selected);selected=sections[0]?.id??null;markDirty();render()});
  root.addEventListener('submit',event=>{document.getElementById('sections_json').value=JSON.stringify(sections);document.getElementById('builder_intent').value=event.submitter?.dataset.intent??'save'});
  render();
})();
