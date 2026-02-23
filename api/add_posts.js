const express = require('express');
const { readJson, writeJson, nextId } = require('./utils/store');
const { requireAuth } = require('./middleware/auth');

const router = express.Router();

router.post('/', requireAuth, async (req, res) => {
  const { title, excerpt, content } = req.body;
  if (!title || !excerpt || !content) {
    return res.status(400).json({ message: 'Vui lòng nhập title, excerpt, content.' });
  }

  const posts = readJson('posts');
  const post = {
    id: nextId(posts),
    title,
    excerpt,
    content,
    authorId: req.user.id,
    authorName: req.user.name,
    createdAt: new Date().toISOString()
  };

  posts.push(post);
  await writeJson('posts', posts);
  return res.status(201).json(post);
});

module.exports = router;
