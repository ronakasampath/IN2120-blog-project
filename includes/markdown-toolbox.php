<?php
/**
 * Professional Markdown Toolbox Component
 * Save as: includes/markdown-toolbox.php
 */
?>

<style>
.markdown-toolbox {
    background: white;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    margin-bottom: 1rem;
    overflow: hidden;
}

.toolbox-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    cursor: pointer;
    user-select: none;
}

.toolbox-header:hover {
    background: #f3f4f6;
}

.toolbox-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.toolbox-toggle {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    color: #6b7280;
    transition: transform 0.2s;
    line-height: 1;
}

.toolbox-toggle.collapsed {
    transform: rotate(-90deg);
}

.toolbox-content {
    padding: 1rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.toolbox-content.hidden {
    display: none;
}

.tool-group {
    display: flex;
    gap: 0.25rem;
    padding-right: 0.5rem;
    border-right: 1px solid #e5e7eb;
}

.tool-group:last-child {
    border-right: none;
}

.tool-btn {
    padding: 0.5rem 0.75rem;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.875rem;
    transition: all 0.15s;
    display: flex;
    align-items: center;
    gap: 0.375rem;
    color: #374151;
    font-weight: 500;
    white-space: nowrap;
}

.tool-btn:hover {
    border-color: #4f46e5;
    background: #f0f4ff;
    color: #4f46e5;
}

.tool-btn:active {
    transform: scale(0.95);
}

.tool-icon {
    font-size: 1rem;
    font-weight: 700;
    line-height: 1;
}

/* Icon styles using CSS */
.icon-bold::before { content: 'B'; font-weight: 700; }
.icon-italic::before { content: 'I'; font-style: italic; font-weight: 600; }
.icon-strike::before { content: 'S'; text-decoration: line-through; font-weight: 600; }
.icon-code::before { content: '<>'; font-family: monospace; font-weight: 600; }
.icon-h1::before { content: 'H₁'; font-weight: 700; }
.icon-h2::before { content: 'H₂'; font-weight: 700; }
.icon-h3::before { content: 'H₃'; font-weight: 700; }
.icon-ul::before { content: '• List'; }
.icon-ol::before { content: '1. List'; }
.icon-link::before { content: '🔗'; }
.icon-image::before { content: '⬜'; font-size: 0.75rem; }
.icon-quote::before { content: '❝'; font-size: 1.1rem; }
.icon-codeblock::before { content: '{ }'; font-family: monospace; font-weight: 600; }
.icon-hr::before { content: '─'; font-weight: 700; }

.quick-reference {
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
    padding: 0.75rem 1rem;
    font-size: 0.75rem;
    color: #6b7280;
}

.quick-reference-title {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #374151;
}

.quick-ref-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 0.5rem;
}

.quick-ref-item {
    font-family: 'Courier New', monospace;
}

.quick-ref-syntax {
    color: #4f46e5;
    font-weight: 600;
}

@media (max-width: 768px) {
    .toolbox-content {
        flex-direction: column;
        align-items: stretch;
    }
    
    .tool-group {
        border-right: none;
        border-bottom: 1px solid #e5e7eb;
        padding-right: 0;
        padding-bottom: 0.5rem;
        justify-content: flex-start;
        flex-wrap: wrap;
    }
    
    .tool-group:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    
    .quick-ref-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="markdown-toolbox">
    <div class="toolbox-header" onclick="toggleMarkdownToolbox()">
        <div class="toolbox-title">
            <span style="font-size: 1rem;">▤</span>
            <span>Formatting Toolbar</span>
        </div>
        <button class="toolbox-toggle" id="mdToggleBtn" type="button">▼</button>
    </div>

    <div class="toolbox-content" id="mdToolboxContent">
        <!-- Text Formatting -->
        <div class="tool-group">
            <button type="button" class="tool-btn" onclick="mdInsertFormat('**', '**', 'bold text')" title="Bold">
                <span class="tool-icon icon-bold"></span>
            </button>
            <button type="button" class="tool-btn" onclick="mdInsertFormat('*', '*', 'italic text')" title="Italic">
                <span class="tool-icon icon-italic"></span>
            </button>
            <button type="button" class="tool-btn" onclick="mdInsertFormat('~~', '~~', 'strikethrough')" title="Strikethrough">
                <span class="tool-icon icon-strike"></span>
            </button>
            <button type="button" class="tool-btn" onclick="mdInsertFormat('`', '`', 'code')" title="Inline Code">
                <span class="tool-icon icon-code"></span>
            </button>
        </div>

        <!-- Headings -->
        <div class="tool-group">
            <button type="button" class="tool-btn" onclick="mdInsertHeading(1)" title="Heading 1">
                <span class="tool-icon icon-h1"></span>
            </button>
            <button type="button" class="tool-btn" onclick="mdInsertHeading(2)" title="Heading 2">
                <span class="tool-icon icon-h2"></span>
            </button>
            <button type="button" class="tool-btn" onclick="mdInsertHeading(3)" title="Heading 3">
                <span class="tool-icon icon-h3"></span>
            </button>
        </div>

        <!-- Lists -->
        <div class="tool-group">
            <button type="button" class="tool-btn" onclick="mdInsertList('bullet')" title="Bullet List">
                <span class="tool-icon icon-ul"></span>
            </button>
            <button type="button" class="tool-btn" onclick="mdInsertList('numbered')" title="Numbered List">
                <span class="tool-icon icon-ol"></span>
            </button>
        </div>

        <!-- Links & Media -->
        <div class="tool-group">
            <button type="button" class="tool-btn" onclick="mdInsertLink()" title="Insert Link">
                <span class="tool-icon icon-link"></span>
            </button>
            <button type="button" class="tool-btn" onclick="mdInsertImage()" title="Insert Image">
                <span class="tool-icon icon-image"></span>
            </button>
        </div>

        <!-- Special -->
        <div class="tool-group">
            <button type="button" class="tool-btn" onclick="mdInsertBlockquote()" title="Quote">
                <span class="tool-icon icon-quote"></span>
            </button>
            <button type="button" class="tool-btn" onclick="mdInsertCodeBlock()" title="Code Block">
                <span class="tool-icon icon-codeblock"></span>
            </button>
            <button type="button" class="tool-btn" onclick="mdInsertHR()" title="Divider">
                <span class="tool-icon icon-hr"></span>
            </button>
        </div>
    </div>

    <div class="quick-reference" id="mdQuickRef" style="display: none;">
        <div class="quick-reference-title">Quick Reference</div>
        <div class="quick-ref-grid">
            <div class="quick-ref-item">
                <span class="quick-ref-syntax">**text**</span> → Bold
            </div>
            <div class="quick-ref-item">
                <span class="quick-ref-syntax">*text*</span> → Italic
            </div>
            <div class="quick-ref-item">
                <span class="quick-ref-syntax"># text</span> → Heading 1
            </div>
            <div class="quick-ref-item">
                <span class="quick-ref-syntax">- item</span> → Bullet
            </div>
            <div class="quick-ref-item">
                <span class="quick-ref-syntax">`code`</span> → Code
            </div>
            <div class="quick-ref-item">
                <span class="quick-ref-syntax">[text](url)</span> → Link
            </div>
        </div>
    </div>
</div>

<script>
function toggleMarkdownToolbox() {
    const content = document.getElementById('mdToolboxContent');
    const btn = document.getElementById('mdToggleBtn');
    const quickRef = document.getElementById('mdQuickRef');
    
    const isHidden = content.classList.contains('hidden');
    
    content.classList.toggle('hidden');
    btn.classList.toggle('collapsed');
    
    if (!isHidden) {
        quickRef.style.display = 'none';
    } else {
        quickRef.style.display = 'block';
    }
}

function getMDTextarea() {
    return document.getElementById('content');
}

function mdInsertFormat(prefix, suffix, placeholder) {
    const textarea = getMDTextarea();
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    const selectedText = text.substring(start, end);
    
    const replacement = selectedText || placeholder;
    const newText = text.substring(0, start) + prefix + replacement + suffix + text.substring(end);
    
    textarea.value = newText;
    textarea.focus();
    
    const newCursorPos = start + prefix.length + replacement.length;
    textarea.setSelectionRange(newCursorPos, newCursorPos);
    
    textarea.dispatchEvent(new Event('input'));
}

function mdInsertHeading(level) {
    const textarea = getMDTextarea();
    const start = textarea.selectionStart;
    const text = textarea.value;
    
    const lineStart = text.lastIndexOf('\n', start - 1) + 1;
    const prefix = '#'.repeat(level) + ' ';
    
    const newText = text.substring(0, lineStart) + prefix + text.substring(lineStart);
    textarea.value = newText;
    textarea.focus();
    textarea.setSelectionRange(lineStart + prefix.length, lineStart + prefix.length);
    
    textarea.dispatchEvent(new Event('input'));
}

function mdInsertList(type) {
    const textarea = getMDTextarea();
    const start = textarea.selectionStart;
    const text = textarea.value;
    
    const prefix = type === 'bullet' ? '- ' : '1. ';
    const lines = [
        prefix + 'Item 1',
        (type === 'bullet' ? '- ' : '2. ') + 'Item 2',
        (type === 'bullet' ? '- ' : '3. ') + 'Item 3'
    ].join('\n');
    
    const newText = text.substring(0, start) + '\n' + lines + '\n' + text.substring(start);
    textarea.value = newText;
    textarea.focus();
    
    textarea.dispatchEvent(new Event('input'));
}

function mdInsertLink() {
    const url = prompt('Enter URL:', 'https://example.com');
    if (!url) return;
    
    const linkText = prompt('Enter link text:', 'Link text');
    if (!linkText) return;
    
    const textarea = getMDTextarea();
    const start = textarea.selectionStart;
    const text = textarea.value;
    
    const insertion = `[${linkText}](${url})`;
    const newText = text.substring(0, start) + insertion + text.substring(start);
    
    textarea.value = newText;
    textarea.focus();
    textarea.dispatchEvent(new Event('input'));
}

function mdInsertImage() {
    const url = prompt('Enter image URL:', 'https://example.com/image.jpg');
    if (!url) return;
    
    const alt = prompt('Enter image description:', 'Image description');
    if (!alt) return;
    
    const textarea = getMDTextarea();
    const start = textarea.selectionStart;
    const text = textarea.value;
    
    const insertion = `![${alt}](${url})`;
    const newText = text.substring(0, start) + insertion + text.substring(start);
    
    textarea.value = newText;
    textarea.focus();
    textarea.dispatchEvent(new Event('input'));
}

function mdInsertBlockquote() {
    const textarea = getMDTextarea();
    const start = textarea.selectionStart;
    const text = textarea.value;
    
    const lineStart = text.lastIndexOf('\n', start - 1) + 1;
    const newText = text.substring(0, lineStart) + '> ' + text.substring(lineStart);
    
    textarea.value = newText;
    textarea.focus();
    textarea.setSelectionRange(lineStart + 2, lineStart + 2);
    textarea.dispatchEvent(new Event('input'));
}

function mdInsertCodeBlock() {
    const textarea = getMDTextarea();
    const start = textarea.selectionStart;
    const text = textarea.value;
    
    const insertion = '```\n// Your code here\n```';
    const newText = text.substring(0, start) + insertion + text.substring(start);
    
    textarea.value = newText;
    textarea.focus();
    textarea.dispatchEvent(new Event('input'));
}

function mdInsertHR() {
    const textarea = getMDTextarea();
    const start = textarea.selectionStart;
    const text = textarea.value;
    
    const insertion = '\n---\n';
    const newText = text.substring(0, start) + insertion + text.substring(start);
    
    textarea.value = newText;
    textarea.focus();
    textarea.dispatchEvent(new Event('input'));
}
</script>