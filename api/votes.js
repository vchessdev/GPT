const express = require('express');
const { readJson, writeJson } = require('./utils/store');
const { requireAuth } = require('./middleware/auth');

const router = express.Router();

router.post('/:postId', requireAuth, (req, res) => {
  const postId = Number(req.params.postId);
  const posts = readJson('posts');
  const post = posts.find((item) => item.id === postId);

  if (!post) {
    return res.status(404).json({ message: 'Không tìm thấy bài viết.' });
  }

  post.votes = Number(post.votes || 0) + 1;
  writeJson('posts', posts);
  return res.json({ postId, votes: post.votes });
});

module.exports = router;
