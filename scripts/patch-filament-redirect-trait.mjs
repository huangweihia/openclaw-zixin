import fs from 'fs';
import path from 'path';

function walk(dir, out = []) {
  for (const name of fs.readdirSync(dir)) {
    const p = path.join(dir, name);
    if (fs.statSync(p).isDirectory()) walk(p, out);
    else if (/Edit.*\.php$/.test(name) || /^Create.*\.php$/.test(name)) out.push(p);
  }
  return out;
}

const base = path.join('app', 'Filament', 'Resources');
const files = walk(base).filter((p) => p.includes(`${path.sep}Pages${path.sep}`));
const useLine =
  'use App\\Filament\\Resources\\Pages\\Concerns\\RedirectsToIndexAfterSave;';
const trait = '    use RedirectsToIndexAfterSave;';

let n = 0;
for (const file of files) {
  let c = fs.readFileSync(file, 'utf8');
  if (c.includes('RedirectsToIndexAfterSave')) continue;
  if (!/^namespace\s+[^;]+;/m.test(c)) continue;
  if (!c.includes(useLine)) {
    c = c.replace(/^(namespace\s+[^;]+;)/m, `$1\n\n${useLine}`);
  }
  c = c.replace(
    /(class\s+\w+\s+extends\s+[^\s{]+\s*\{)\s*\n/,
    `$1\n${trait}\n\n`
  );
  fs.writeFileSync(file, c);
  n++;
  console.log(file);
}
console.log('patched', n);
