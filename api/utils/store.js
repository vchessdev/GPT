const fs = require('fs');
const path = require('path');

const DATA_DIR = path.join(__dirname, '..', '..', 'data');

const FILES = {
  users: path.join(DATA_DIR, 'users.json'),
  posts: path.join(DATA_DIR, 'posts.json'),
  comments: path.join(DATA_DIR, 'comments.json')
};

const writeQueue = {};

function readJson(type) {
  return JSON.parse(fs.readFileSync(FILES[type], 'utf-8'));
}

function writeJson(type, data) {
  if (!writeQueue[type]) {
    writeQueue[type] = Promise.resolve();
  }
  writeQueue[type] = writeQueue[type].then(() => {
    return new Promise((resolve, reject) => {
      fs.writeFile(FILES[type], JSON.stringify(data, null, 2), (err) => {
        if (err) reject(err);
        else resolve();
      });
    });
  });
  return writeQueue[type];
}

function nextId(items) {
  return items.length ? Math.max(...items.map((item) => item.id)) + 1 : 1;
}

module.exports = {
  readJson,
  writeJson,
  nextId
};
