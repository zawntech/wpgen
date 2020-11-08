const fs = require('fs');
const path = require('path');
const buildScss = require('node-sass');
const glob = require('glob-promise');
const bbModulesPath = path.resolve(__dirname + '/../../bb-modules/');
const target = `${bbModulesPath}/**/*.scss`;
const md5 = require('md5');

glob(target).then(processFiles);

async function processFiles(files) {
    for (let i in files) {
        const file = files[i],
            outFile = file.replace('.scss', '.css');
        let hash;
        if (fs.existsSync(outFile)) {
            hash = md5(fs.readFileSync(outFile, 'utf8'));
        }
        await buildScss.render({file, outFile}, function (err, result) {
            let newHash = md5(result.css);
            if (hash !== newHash) {
                fs.writeFile(outFile, result.css, function (err) {
                    if (!err) {
                    }
                });
            }
        });
    }
}
