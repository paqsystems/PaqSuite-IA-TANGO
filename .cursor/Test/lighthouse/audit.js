import lighthouse from 'lighthouse';
import * as chromeLauncher from 'chrome-launcher';
import fs from 'fs';

(async () => {
  const chrome = await chromeLauncher.launch({chromeFlags: ['--headless']});
  
  const options = {
    logLevel: 'info',
    output: 'html',
    onlyCategories: ['performance', 'accessibility', 'seo', 'best-practices'],
    port: chrome.port
  };
  
  const runnerResult = await lighthouse('https://www.google.com', options);
  
  const reportHtml = runnerResult.report;
  fs.writeFileSync('reporte-lighthouse.html', reportHtml);
  
  console.log('\n=== RESULTADOS DE LA AUDITORÍA ===\n');
  console.log('URL analizada:', runnerResult.lhr.finalUrl);
  console.log('\nScores:');
  console.log('Performance:', Math.round(runnerResult.lhr.categories.performance.score * 100));
  console.log('Accessibility:', Math.round(runnerResult.lhr.categories.accessibility.score * 100));
  console.log('Best Practices:', Math.round(runnerResult.lhr.categories['best-practices'].score * 100));
  console.log('SEO:', Math.round(runnerResult.lhr.categories.seo.score * 100));
  
  console.log('\n✅ Reporte guardado en: reporte-lighthouse.html');
  
  await chrome.kill();
})();