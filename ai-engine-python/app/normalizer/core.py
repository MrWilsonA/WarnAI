from pathlib import Path
import re
EXT={'.py','.php','.js','.ts','.md','.txt','.yaml','.yml','.json','.toml','.sh','.sql','.html','.css'}
def normalize_path(p:Path):
 if p.suffix.lower() not in EXT:return None
 return '# '+p.name+'\n\n'+re.sub(r'\n\s*\n\s*\n+','\n\n',p.read_text(encoding='utf-8',errors='ignore')).strip()
