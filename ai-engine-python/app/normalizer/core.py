from pathlib import Path
import re
TEXT_EXT={'.py','.php','.js','.ts','.jsx','.tsx','.java','.go','.rs','.rb','.md','.txt','.yaml','.yml','.json','.toml','.ini','.sh','.sql','.html','.css'}
def normalize_path(p:Path):
 if p.suffix.lower() not in TEXT_EXT:return None
 text=p.read_text(encoding='utf-8',errors='ignore')
 text=re.sub(r'<!--.*?-->','',text,flags=re.S)
 text=re.sub(r'//\s*(TODO|FIXME).*','',text,flags=re.I)
 text=re.sub(r'\n\s*\n\s*\n+','\n\n',text).strip()
 return f'# {p.name}\n\n{text}'
