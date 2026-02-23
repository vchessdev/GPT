const express = require('express');
const { readJson, writeJson } = require('./utils/store');
const { requireAuth } = require('./middleware/auth');

const router = express.Router();

router.delete('/:id', requireAuth, (req, res) => {
  const commentId = Number(req.params.id);
  const comments = readJson('comments');
  const comment = comments.find((item) => item.id === commentId);

  if (!comment) {
    return res.status(404).json({ message: 'Không tìm thấy comment.' });
  }

  if (comment.userId !== req.user.id) {
    return res.status(403).json({ message: 'Bạn không có quyền xoá comment này.' });
  }

  writeJson('comments', comments.filter((item) => item.id !== commentId));
  return res.json({ message: 'Đã xoá comment.' });
});

module.exports = router;
