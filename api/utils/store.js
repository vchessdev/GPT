const fs = require('fs');
const path = require('path');

const DATA_DIR = path.join(__dirname, '..', '..', 'data');

const FILES = {
  users: path.join(DATA_DIR, 'users.json'),
  posts: path.join(DATA_DIR, 'posts.json'),
  comments: path.join(DATA_DIR, 'comments.json')
};

function readJson(type) {
  return JSON.parse(fs.readFileSync(FILES[type], 'utf-8'));
}

function writeJson(type, data) {
  fs.writeFileSync(FILES[type], JSON.stringify(data, null, 2));
}

function nextId(items) {
  return items.length ? Math.max(...items.map((item) => item.id)) + 1 : 1;
}

module.exports = {
  readJson,
  writeJson,
  nextId
};
