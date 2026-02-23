const express = require('express');
const { readJson, writeJson } = require('./utils/store');
const { requireAuth } = require('./middleware/auth');

const router = express.Router();

router.delete('/:id', requireAuth, async (req, res) => {
  const postId = Number(req.params.id);
  const posts = readJson('posts');
  const post = posts.find((item) => item.id === postId);

  if (!post) {
    return res.status(404).json({ message: 'Không tìm thấy bài viết.' });
  }

  if (post.authorId !== req.user.id) {
    return res.status(403).json({ message: 'Bạn không có quyền xoá bài viết này.' });
  }

  const nextPosts = posts.filter((item) => item.id !== postId);
  const comments = readJson('comments').filter((item) => item.postId !== postId);

  await Promise.all([
    writeJson('posts', nextPosts),
    writeJson('comments', comments)
  ]);
  return res.json({ message: 'Đã xoá bài viết.' });
});

module.exports = router;
