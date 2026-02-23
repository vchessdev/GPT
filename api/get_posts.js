const express = require('express');
const { readJson } = require('./utils/store');

const router = express.Router();

router.get('/', (req, res) => {
  const posts = readJson('posts');
  const comments = readJson('comments');

  const payload = posts
    .map((post) => ({
      ...post,
      commentsCount: comments.filter((comment) => comment.postId === post.id).length
    }))
    .sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));

  return res.json(payload);
});

router.get('/:id', (req, res) => {
  const posts = readJson('posts');
  const post = posts.find((item) => item.id === Number(req.params.id));
  if (!post) {
    return res.status(404).json({ message: 'Không tìm thấy bài viết.' });
  }
  return res.json(post);
});

module.exports = router;
