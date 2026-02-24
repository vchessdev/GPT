<?php
/**
 * DevDA Blog System - Main JavaScript App
 */

// Base API URL
const API_URL = document.currentScript?.getAttribute('data-api-url') || '/blog/api';

/**
 * Fetch wrapper with error handling
 */
async function apiCall(endpoint, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        }
    };

    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(endpoint, options);
        const result = await response.json();
        return { success: response.ok, data: result, status: response.status };
    } catch (error) {
        console.error('API Error:', error);
        return { success: false, error: 'Network error', status: 0 };
    }
}

/**
 * Format date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return date.toLocaleDateString('vi-VN', options);
}

/**
 * Sanitize HTML
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

/**
 * Show notification
 */
function showNotification(message, type = 'success', duration = 3000) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background: ${type === 'success' ? '#27ae60' : '#e74c3c'};
        color: white;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        z-index: 9999;
        max-width: 400px;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, duration);
}

/**
 * Check login status
 */
async function checkLoginStatus() {
    const response = await apiCall('/blog/api/auth.php?action=check');
    return response.data.loggedIn ? response.data.user : null;
}

/**
 * Logout
 */
async function logout() {
    const response = await apiCall('/blog/api/auth.php?action=logout');
    if (response.success) {
        window.location.reload();
    }
}

/**
 * Load posts list
 */
async function loadPosts(page = 1, category = null, tag = null) {
    let url = `/blog/api/posts.php?action=list&page=${page}&limit=10`;
    if (category) url += `&category=${category}`;
    if (tag) url += `&tag=${tag}`;

    const response = await apiCall(url);
    return response.data;
}

/**
 * Get single post
 */
async function getPost(id) {
    const response = await apiCall(`/blog/api/posts.php?action=get&id=${id}`);
    return response.data;
}

/**
 * Create post
 */
async function createPost(title, content, category, tags, status = 'draft') {
    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('title', title);
    formData.append('content', content);
    formData.append('category', category);
    formData.append('tags', tags);
    formData.append('status', status);

    const response = await fetch('/blog/api/posts.php', {
        method: 'POST',
        body: formData
    });

    return await response.json();
}

/**
 * Update post
 */
async function updatePost(id, updates) {
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('id', id);
    
    Object.keys(updates).forEach(key => {
        formData.append(key, updates[key]);
    });

    const response = await fetch('/blog/api/posts.php', {
        method: 'POST',
        body: formData
    });

    return await response.json();
}

/**
 * Delete post
 */
async function deletePost(id) {
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    const response = await fetch('/blog/api/posts.php', {
        method: 'POST',
        body: formData
    });

    return await response.json();
}

/**
 * Search posts
 */
async function searchPosts(query) {
    const response = await apiCall(`/blog/api/posts.php?action=search&q=${encodeURIComponent(query)}`);
    return response.data;
}

/**
 * Get comments
 */
async function getComments(postId) {
    const response = await apiCall(`/blog/api/comments.php?action=list&post_id=${postId}`);
    return response.data;
}

/**
 * Create comment
 */
async function createComment(postId, content, parentId = null) {
    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('post_id', postId);
    formData.append('content', content);
    if (parentId) formData.append('parent_id', parentId);

    const response = await fetch('/blog/api/comments.php', {
        method: 'POST',
        body: formData
    });

    return await response.json();
}

/**
 * Delete comment
 */
async function deleteComment(id) {
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    const response = await fetch('/blog/api/comments.php', {
        method: 'POST',
        body: formData
    });

    return await response.json();
}

/**
 * Vote post
 */
async function votePost(postId, type) {
    const formData = new FormData();
    formData.append('action', 'vote');
    formData.append('post_id', postId);
    formData.append('type', type);

    const response = await fetch('/blog/api/votes.php', {
        method: 'POST',
        body: formData
    });

    return await response.json();
}

/**
 * Get votes
 */
async function getVotes(postId) {
    const response = await apiCall(`/blog/api/votes.php?action=getVotes&post_id=${postId}`);
    return response.data;
}

/**
 * Upload file
 */
async function uploadFile(file, type = 'docs') {
    const formData = new FormData();
    formData.append('action', 'upload');
    formData.append('file', file);
    formData.append('type', type);

    const response = await fetch('/blog/api/files.php', {
        method: 'POST',
        body: formData
    });

    return await response.json();
}

// Export functions if using modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        apiCall, formatDate, escapeHtml, showNotification,
        checkLoginStatus, logout, loadPosts, getPost,
        createPost, updatePost, deletePost, searchPosts,
        getComments, createComment, deleteComment,
        votePost, getVotes, uploadFile
    };
}
