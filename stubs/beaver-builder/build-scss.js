const fs = require('fs');
const buildScss = require('sass');
const glob = require('glob');
const target = `bb-modules/**/*.scss`;
const md5 = require('md5');

glob(target).then(processFiles);

async function processFiles(files) {

    for (let i in files) {

        const file = files[i];
        const outFile = file.replace('.scss', '.css');

        // Create a hash of the existing output to see if recompilation is necessary;
        let hash;
        if (fs.existsSync(outFile)) {
            hash = md5(fs.readFileSync(outFile, 'utf8'));
        }

        // Compile sass.
        try {
            const result = buildScss.compile(file);
            const newHash = md5(result.css);
            if (!hash || hash !== newHash) {
                fs.writeFile(outFile, result.css, {}, () => {});
            }
        } catch (error) {
            console.error(error);
        }
    }
}
