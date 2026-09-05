from rank_bm25 import BM25Okapi
class HybridIndex:
 def __init__(self):self.documents=[];self.tokens=[];self.bm25=None
 def add(self,path,text):self.documents.append({'path':path,'markdown':text});self.tokens.append(text.lower().split());self.bm25=BM25Okapi(self.tokens)
 def search(self,q,n=5):
  if not self.documents:return []
  s=self.bm25.get_scores(q.lower().split());ids=sorted(range(len(s)),key=lambda i:s[i],reverse=True)[:n]
  return [{**self.documents[i],'score':round(float(s[i]),4)} for i in ids]
