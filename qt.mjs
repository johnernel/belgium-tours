import puppeteer from 'puppeteer-core';
import { Launcher } from 'chrome-launcher';
const b = await puppeteer.launch({ executablePath: Launcher.getFirstInstallation(), headless: 'new' });
for (const [n,w] of [['m390',390],['m360',360],['d',1440]]) { const p = await b.newPage(); await p.setViewport({ width: w, height: 900, isMobile:w<700, deviceScaleFactor:2 });
 await p.goto('http://127.0.0.1:8000/', { waitUntil:'networkidle2' }); await new Promise(r=>setTimeout(r,800));
 const q = await p.$('#quiz'); await q.screenshot({path:'qt-'+n+'.png'}); await p.close(); }
await b.close();
