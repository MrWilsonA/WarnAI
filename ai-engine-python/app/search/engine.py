from rank_bm25 import BM25Okapi
import re, math
class HybridIndex:
 def __init__(self): self.documents=[]; self.tokens=[]; self.bm25=None; self.queries=0
 def add(self,path,text):
  self.documents.append({'path':path,'markdown':text,'tokens':len(text.split())}); self.tokens.append(re.findall(r'\w+',text.lower())); self.bm25=BM25Okapi(self.tokens)
 def search(self,q,n=5):
  self.queries+=1
  if not self.documents:return []
  terms=re.findall(r'\w+',q.lower()); scores=self.bm25.get_scores(terms); ids=sorted(range(len(scores)),key=lambda i:scores[i],reverse=True)[:n]
  return [{**self.documents[i],'score':round(float(scores[i]),4)} for i in ids]
 def analytics(self):
  raw=sum(len(d['markdown']) for d in self.documents); tokens=sum(d['tokens'] for d in self.documents)
  return {'documents':len(self.documents),'queries':self.queries,'markdown_chars':raw,'estimated_tokens':tokens,'avg_tokens':round(tokens/len(self.documents),1) if self.documents else 0}
