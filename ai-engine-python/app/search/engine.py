from rank_bm25 import BM25Okapi
import re
try:
 from sentence_transformers import SentenceTransformer
except ImportError: SentenceTransformer=None
class HybridIndex:
 def __init__(self):
  self.documents=[];self.tokens=[];self.bm25=None;self.queries=0;self.encoder=None;self.vectors=[]
  try:self.encoder=SentenceTransformer('all-MiniLM-L6-v2') if SentenceTransformer else None
  except Exception:self.encoder=None
 def add(self,path,text):
  self.documents.append({'path':path,'markdown':text,'tokens':len(text.split())});self.tokens.append(re.findall(r'\w+',text.lower()));self.bm25=BM25Okapi(self.tokens)
  if self.encoder:self.vectors.append(self.encoder.encode(text,normalize_embeddings=True))
 def search(self,q,n=5):
  self.queries+=1
  if not self.documents:return []
  terms=re.findall(r'\w+',q.lower());lex=self.bm25.get_scores(terms)
  if self.encoder and self.vectors:
   import numpy as np
   semantic=np.dot(np.array(self.vectors),self.encoder.encode(q,normalize_embeddings=True)); mx=max(float(max(lex)),1e-9); scores=.45*(lex/mx)+.55*semantic
  else:scores=lex
  ids=sorted(range(len(scores)),key=lambda i:scores[i],reverse=True)[:n]
  return [{**self.documents[i],'score':round(float(scores[i]),4)} for i in ids]
 def analytics(self):
  tokens=sum(d['tokens'] for d in self.documents)
  return {'documents':len(self.documents),'queries':self.queries,'estimated_tokens':tokens,'avg_tokens':round(tokens/len(self.documents),1) if self.documents else 0,'deep_learning_embeddings':bool(self.encoder),'retrieval_mode':'hybrid-semantic-bm25' if self.encoder else 'bm25-fallback'}
