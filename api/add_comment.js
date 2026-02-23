const express = require('express');
const { readJson, writeJson, nextId } = require('./utils/store');
const { requireAuth } = require('./middleware/auth');

const router = express.Router();

router.post('/:postId', requireAuth, (req, res) => {
  const { content } = req.body;
  const postId = Number(req.params.postId);

  if (!content) {
    return res.status(400).json({ message: 'Nội dung comment không được trống.' });
  }

  const posts = readJson('posts');
  const targetPost = posts.find((post) => post.id === postId);
  if (!targetPost) {
    return res.status(404).json({ message: 'Không tìm thấy bài viết.' });
  }

  const comments = readJson('comments');
  const comment = {
    id: nextId(comments),
    postId,
    userId: req.user.id,
    authorName: req.user.name,
    content,
    createdAt: new Date().toISOString()
  };

  comments.push(comment);
  writeJson('comments', comments);
  return res.status(201).json(comment);
});

module.exports = router;
