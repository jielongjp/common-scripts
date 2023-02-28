const puppeteer = require('puppeteer');
const fs = require('fs');

// read from a text file instead of array
// const linkListFile = 'linklist.txt';


// links to crawl below or use txt file like above
const linkList = [
    'https://jordharris.com',
    'https://jordharris.com/experience/',
    'https://jordharris.com/projects/'
];

(async () => {
  // read the link list file and split by new lines
//  const linkList = fs.readFileSync(linkListFile).toString().split('\n');

  const browser = await puppeteer.launch();

  try {

    for (let i = 0; i < linkList.length; i++) {
      const url = linkList[i].trim();
  
      if (url.length === 0) {
        continue;
      }
  
      console.log(`***** \n URL: ${url}\n*****`);
  
      const page = await browser.newPage();
      await page.goto(url, { waitUntil: 'networkidle0' });
  
      const links = await page.$$eval('a', (elements) =>
        elements.map((el) => el.href)
        .filter((href) => href !== '' && !href.startsWith('#') && !href.startsWith('javascript:void'))
      );
  
      console.log(links.join('\n'));
  
      await page.close();
    }
      
  } catch (error) {

    console.log(error);
    
  } finally {
    await browser.close();
  }

})();
