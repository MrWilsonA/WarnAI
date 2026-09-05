from fastapi import FastAPI,UploadFile,File,HTTPException
from fastapi.responses import HTMLResponse
from pydantic import BaseModel
from pathlib import Path
import tempfile,zipfile,time
from .normalizer.core import normalize_path
from .search.engine import HybridIndex
app=FastAPI(title='WarnAI',version='1.0.0',description='Private workspace intelligence engine')
index=HybridIndex()
class Query(BaseModel): query:str; limit:int=5
@app.get('/health')
def health(): return {'status':'ok','service':'warnai-engine','analytics':index.analytics()}
@app.get('/',response_class=HTMLResponse)
def dashboard(): return (Path(__file__).parent/'dashboard.html').read_text(encoding='utf-8')
@app.post('/normalize')
async def normalize(file:UploadFile=File(...)):
 started=time.perf_counter(); data=await file.read()
 if len(data)>50*1024*1024: raise HTTPException(413,'File exceeds 50 MB limit')
 with tempfile.TemporaryDirectory() as td:
  p=Path(td)/file.filename;p.write_bytes(data);root=p
  if zipfile.is_zipfile(p): root=Path(td)/'src';zipfile.ZipFile(p).extractall(root)
  out=[]
  for f in (root.rglob('*') if root.is_dir() else [root]):
   if f.is_file():
    text=normalize_path(f)
    if text: out.append({'path':f.name,'markdown':text});index.add(str(f),text)
 return {'files':out,'count':len(out),'elapsed_ms':round((time.perf_counter()-started)*1000,2),'analytics':index.analytics()}
@app.post('/search')
def search(payload:Query): return {'query':payload.query,'results':index.search(payload.query,max(1,min(payload.limit,20))),'analytics':index.analytics()}
@app.get('/analytics')
def analytics(): return index.analytics()
