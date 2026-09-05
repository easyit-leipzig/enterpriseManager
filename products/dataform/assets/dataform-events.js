// easyIT DataFormActionContext 1.0 – PUBLISH17
(()=>{
  const parseJson=id=>{try{return JSON.parse(document.getElementById(id)?.textContent||'{}')}catch(e){console.error('DataForm JSON',e);return {}}};
  const handlers=parseJson('df-event-handlers');
  const serverContext=parseJson('df-action-context');
  const clone=value=>{try{return structuredClone(value)}catch(e){return JSON.parse(JSON.stringify(value||{}))}};
  const base=serverContext&&serverContext.schema?serverContext:{
    schema:'easyit.dataform.action-context',schema_version:'1.0',object_name:'dataformContext',
    action:{name:'open',phase:'before',trigger:'user',timestamp:new Date().toISOString()},
    project:window.DF_CONTEXT_META?.project||{},dataform:window.DF_CONTEXT_META?.dataform||{},
    context:{mode:'view',is_new_record:false,is_dirty:false,preview:false,current_record:{id:Number(document.body?.dataset.currentRecord||0),page:1},parent:null,relation:null},
    record:{id:Number(document.body?.dataset.currentRecord||0),values:{},original_values:{},changes:{},dirty_fields:[]},
    fields:{},validation:{valid:true,errors:[],warnings:[]},ui:{view_mode:document.body?.dataset.viewMode||'table',page:1,records_per_page:20,search:'',active_record_id:Number(document.body?.dataset.currentRecord||0),dialog_open:false},event:{cancel:false,message:null,data:{}},result:null
  };
  function valuesFromForm(form){
    const out={}; if(!form)return out;
    const fd=new FormData(form);
    for(const [key,value] of fd.entries()){
      if(['csrf','action','project','dataform','record','active_record','mode','embed','preview','parent_relation','parent_record'].includes(key))continue;
      const m=key.match(/^values\[([^\]]+)\](?:\[\])?$/); const name=m?m[1]:key;
      if(Object.prototype.hasOwnProperty.call(out,name)){if(!Array.isArray(out[name]))out[name]=[out[name]];out[name].push(value instanceof File?value.name:value);}else out[name]=value instanceof File?value.name:value;
    }
    return out;
  }
  function contextFor(name,phase='before',form=null){
    const ctx=clone(base);ctx.action=ctx.action||{};ctx.action.name=name.replace(/^before_|^after_/,'');ctx.action.phase=phase;ctx.action.timestamp=new Date().toISOString();ctx.object_name='dataformContext';ctx.event=ctx.event||{cancel:false,message:null,data:{}};
    if(form){const vals=valuesFromForm(form);ctx.record=ctx.record||{};ctx.record.values={...(ctx.record.values||{}),...vals};const rid=Number(form.querySelector('[name="record"]')?.value||ctx.record.id||0);ctx.record.id=rid;ctx.context=ctx.context||{};ctx.context.current_record={...(ctx.context.current_record||{}),id:rid};ctx.context.mode=rid>0?'edit':'create';ctx.context.is_new_record=rid<1;ctx.context.is_dirty=true;}
    return ctx;
  }
  function run(name,ctx,event=null){
    const code=String(handlers[name]||'').trim();if(!code)return true;
    const dataformContext=ctx||contextFor(name,name.startsWith('after_')?'after':'before');
    try{
      const result=(new Function('dataformContext','detail','event','"use strict";\n'+code))(dataformContext,dataformContext,event);
      return result!==false && dataformContext?.event?.cancel!==true;
    }catch(err){console.error('DataForm event '+name,err,dataformContext);return true;}
  }
  window.easyitDataFormEvent=(name,dataformContext=null,event=null)=>run(name,dataformContext||contextFor(name),event);
  window.dataformContext=base;
  function start(){
    if(serverContext?.action?.name==='save'&&serverContext?.action?.phase==='after')run('after_save',clone(serverContext));
    else run('open',contextFor('open','after'));
    document.addEventListener('easyit-dataform-current-record',e=>{const ctx=contextFor('record_change','after');const id=Number(e.detail?.recordId||0);ctx.record.id=id;ctx.context.current_record={...(ctx.context.current_record||{}),id};ctx.ui.active_record_id=id;run('record_change',ctx,e);});
    document.addEventListener('submit',e=>{const action=e.target.querySelector('input[name="action"]')?.value||'';if(action==='save_record'&&!run('before_save',contextFor('save','before',e.target),e))e.preventDefault();if((action==='delete_record'||action==='bulk_delete')&&!run('before_delete',contextFor('delete','before',e.target),e))e.preventDefault();},true);
    window.addEventListener('pagehide',e=>run('close',contextFor('close','before'),e));
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',start,{once:true});else start();
})();
