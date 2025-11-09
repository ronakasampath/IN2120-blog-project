/**
 * Main JavaScript File - COMPLETE WORKING VERSION
 * Save as: assets/js/main.js
 */

/**
 * Enhanced Markdown Renderer
 */
function renderMarkdown(markdown) {
    if (!markdown) return '';
    
    let html = markdown;
    
    // Normalize line breaks
    html = html.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
    
    // Escape HTML first
    html = html.replace(/&/g, '&amp;')
               .replace(/</g, '&lt;')
               .replace(/>/g, '&gt;');
    
    // Split into lines for processing
    const lines = html.split('\n');
    const processed = [];
    let inList = false;
    let inOrderedList = false;
    let inCodeBlock = false;
    let codeBlockContent = '';
    
    for (let i = 0; i < lines.length; i++) {
        let line = lines[i];
        
        // Handle code blocks
        if (line.trim().startsWith('```')) {
            if (!inCodeBlock) {
                inCodeBlock = true;
                codeBlockContent = '';
                continue;
            } else {
                processed.push('<pre><code>' + codeBlockContent.trim() + '</code></pre>');
                inCodeBlock = false;
                codeBlockContent = '';
                continue;
            }
        }
        
        if (inCodeBlock) {
            codeBlockContent += line + '\n';
            continue;
        }
        
        // Headers (must be at start of line with space after #)
        if (line.match(/^### (.+)$/)) {
            if (inList) { processed.push('</ul>'); inList = false; }
            if (inOrderedList) { processed.push('</ol>'); inOrderedList = false; }
            processed.push(line.replace(/^### (.+)$/, '<h3>$1</h3>'));
            continue;
        }
        if (line.match(/^## (.+)$/)) {
            if (inList) { processed.push('</ul>'); inList = false; }
            if (inOrderedList) { processed.push('</ol>'); inOrderedList = false; }
            processed.push(line.replace(/^## (.+)$/, '<h2>$1</h2>'));
            continue;
        }
        if (line.match(/^# (.+)$/)) {
            if (inList) { processed.push('</ul>'); inList = false; }
            if (inOrderedList) { processed.push('</ol>'); inOrderedList = false; }
            processed.push(line.replace(/^# (.+)$/, '<h1>$1</h1>'));
            continue;
        }
        
        // Unordered lists
        if (line.match(/^[-*+] (.+)$/)) {
            if (inOrderedList) { processed.push('</ol>'); inOrderedList = false; }
            if (!inList) {
                processed.push('<ul>');
                inList = true;
            }
            processed.push(line.replace(/^[-*+] (.+)$/, '<li>$1</li>'));
            continue;
        }
        
        // Ordered lists
        if (line.match(/^\d+\. (.+)$/)) {
            if (inList) { processed.push('</ul>'); inList = false; }
            if (!inOrderedList) {
                processed.push('<ol>');
                inOrderedList = true;
            }
            processed.push(line.replace(/^\d+\. (.+)$/, '<li>$1</li>'));
            continue;
        }
        
        // Close lists if line doesn't match list pattern
        if (inList && !line.match(/^[-*+] /)) {
            processed.push('</ul>');
            inList = false;
        }
        if (inOrderedList && !line.match(/^\d+\. /)) {
            processed.push('</ol>');
            inOrderedList = false;
        }
        
        // Blockquotes
        if (line.match(/^&gt; (.+)$/)) {
            processed.push(line.replace(/^&gt; (.+)$/, '<blockquote>$1</blockquote>'));
            continue;
        }
        
        // Horizontal rules
        if (line.match(/^---$/) || line.match(/^\*\*\*$/)) {
            processed.push('<hr>');
            continue;
        }
        
        // Empty lines
        if (line.trim() === '') {
            processed.push('<br>');
            continue;
        }
        
        // Regular text
        processed.push(line);
    }
    
    // Close any open lists
    if (inList) processed.push('</ul>');
    if (inOrderedList) processed.push('</ol>');
    
    // Join processed lines
    html = processed.join('\n');
    
    // Inline formatting
    html = html.replace(/\*\*\*(.+?)\*\*\*/g, '<strong><em>$1</em></strong>');
    html = html.replace(/___(.+?)___/g, '<strong><em>$1</em></strong>');
    html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    html = html.replace(/__(.+?)__/g, '<strong>$1</strong>');
    html = html.replace(/\*(.+?)\*/g, '<em>$1</em>');
    html = html.replace(/_(.+?)_/g, '<em>$1</em>');
    html = html.replace(/~~(.+?)~~/g, '<del>$1</del>');
    
    // Links
    html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>');
    
    // Images
    html = html.replace(/!\[([^\]]*)\]\(([^)]+)\)/g, '<img src="$2" alt="$1" style="max-width: 100%; height: auto; border-radius: 8px; margin: 1rem 0;">');
    
    // Inline code
    html = html.replace(/`([^`]+)`/g, '<code>$1</code>');
    
    // Wrap in paragraphs
    html = '<p>' + html + '</p>';
    
    // Clean up paragraph tags around block elements
    html = html.replace(/<p>\s*<h([1-6])>/g, '<h$1>');
    html = html.replace(/<\/h([1-6])>\s*<\/p>/g, '</h$1>');
    html = html.replace(/<p>\s*<ul>/g, '<ul>');
    html = html.replace(/<\/ul>\s*<\/p>/g, '</ul>');
    html = html.replace(/<p>\s*<ol>/g, '<ol>');
    html = html.replace(/<\/ol>\s*<\/p>/g, '</ol>');
    html = html.replace(/<p>\s*<pre>/g, '<pre>');
    html = html.replace(/<\/pre>\s*<\/p>/g, '</pre>');
    html = html.replace(/<p>\s*<blockquote>/g, '<blockquote>');
    html = html.replace(/<\/blockquote>\s*<\/p>/g, '</blockquote>');
    html = html.replace(/<p>\s*<hr>\s*<\/p>/g, '<hr>');
    
    // Clean up empty paragraphs
    html = html.replace(/<p>\s*<\/p>/g, '');
    
    // Convert multiple <br> to paragraph breaks
    html = html.replace(/(<br>\s*){2,}/g, '</p><p>');
    
    return html;
}

/**
 * Show notification
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '1000';
    notification.style.minWidth = '300px';
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

/**
 * Confirm action
 */
function confirmAction(message) {
    return confirm(message);
}

/**
 * Format date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleDateString('en-US', options);
}

/**
 * Debounce function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    console.log('Blog app loaded successfully!');
    console.log('Markdown renderer ready');
    
    // Test markdown function
    if (typeof renderMarkdown === 'function') {
        console.log('✅ renderMarkdown function is available');
    } else {
        console.error('❌ renderMarkdown function is NOT available');
    }
});