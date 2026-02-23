const express = require('express');
const { readJson } = require('./utils/store');

const router = express.Router();

router.get('/:postId', (req, res) => {
  const comments = readJson('comments')
    .filter((item) => item.postId === Number(req.params.postId))
    .sort((a, b) => new Date(a.createdAt) - new Date(b.createdAt));

  return res.json(comments);
});

module.exports = router;
