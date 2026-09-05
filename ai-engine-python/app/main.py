from fastapi import FastAPI,UploadFile,File
from pathlib import Path
import tempfile,zipfile,time
from .normalizer.core import normalize_path
from .search.engine import HybridIndex
app=FastAPI(title='warn.ai local engine'); index=HybridIndex()
@app.get('/health')
def health(): return {'status':'ok','documents':len(index.documents)}
@app.post('/normalize')
async def normalize(file:UploadFile=File(...)):
 s=time.perf_counter(); data=await file.read()
 with tempfile.TemporaryDirectory() as td:
  p=Path(td)/file.filename;p.write_bytes(data);root=p
  if zipfile.is_zipfile(p):
   root=Path(td)/'src';zipfile.ZipFile(p).extractall(root)
  out=[]
  for f in (root.rglob('*') if root.is_dir() else [root]):
   if f.is_file():
    t=normalize_path(f)
    if t: out.append({'path':f.name,'markdown':t});index.add(str(f),t)
 return {'files':out,'count':len(out),'elapsed_ms':round((time.perf_counter()-s)*1000,2)}
@app.post('/search')
def search(payload:dict): return {'results':index.search(payload.get('query',''),payload.get('limit',5))}
