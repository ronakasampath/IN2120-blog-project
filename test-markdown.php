<?php
/**
 * Test Markdown Rendering
 * Save as: test-markdown.php (in root)
 * Visit: http://localhost/Idea-canvas/test-markdown.php
 */

require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Markdown Test</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <style>
        .test-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 2rem;
        }
        .test-section {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .test-input {
            width: 100%;
            min-height: 200px;
            padding: 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            margin-bottom: 1rem;
        }
        .test-output {
            padding: 1.5rem;
            background: #f9fafb;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            min-height: 200px;
        }
        .status {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        .status.success {
            background: #d1fae5;
            color: #065f46;
        }
        .status.error {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🧪 Markdown Rendering Test</h1>
        
        <div id="statusBox"></div>
        
        <div class="test-section">
            <h2>Test Input</h2>
            <textarea class="test-input" id="testInput" placeholder="Type markdown here..."># Hello World

This is **bold** and this is *italic*.

## Features
- Item 1
- Item 2
- Item 3

### Code Example
```
const test = "hello";
```

> This is a blockquote

[Link to Google](https://google.com)</textarea>
            
            <button onclick="testMarkdown()" style="padding: 0.75rem 1.5rem; background: #4f46e5; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                Test Markdown
            </button>
        </div>
        
        <div class="test-section">
            <h2>Rendered Output</h2>
            <div class="test-output" id="testOutput">
                Click "Test Markdown" to see rendered output...
            </div>
        </div>
        
        <div class="test-section">
            <h2>Console Log</h2>
            <div style="background: #1f2937; color: #10b981; padding: 1rem; border-radius: 8px; font-family: monospace; font-size: 0.875rem;" id="consoleLog">
                Open browser console (F12) for detailed logs...
            </div>
        </div>
    </div>

    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    
    <script>
        console.log('=== MARKDOWN TEST PAGE ===');
        console.log('SITE_URL:', '<?php echo SITE_URL; ?>');
        console.log('Testing renderMarkdown function...');
        
        const statusBox = document.getElementById('statusBox');
        const consoleLog = document.getElementById('consoleLog');
        
        function logToPage(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            consoleLog.innerHTML += `<div style="color: ${type === 'error' ? '#ef4444' : '#10b981'}">[${timestamp}] ${message}</div>`;
        }
        
        // Check if renderMarkdown exists
        if (typeof renderMarkdown === 'function') {
            statusBox.innerHTML = '<div class="status success">✅ renderMarkdown function is loaded and ready!</div>';
            logToPage('✅ renderMarkdown function found');
            console.log('✅ renderMarkdown function is available');
        } else {
            statusBox.innerHTML = '<div class="status error">❌ renderMarkdown function NOT found! Check if main.js is loading correctly.</div>';
            logToPage('❌ renderMarkdown function NOT found', 'error');
            console.error('❌ renderMarkdown function is NOT available');
            console.error('Check if main.js is loaded correctly at:', '<?php echo SITE_URL; ?>/assets/js/main.js');
        }
        
        function testMarkdown() {
            const input = document.getElementById('testInput').value;
            const output = document.getElementById('testOutput');
            
            logToPage('Testing markdown conversion...');
            console.log('Input:', input);
            
            try {
                if (typeof renderMarkdown !== 'function') {
                    throw new Error('renderMarkdown function not found');
                }
                
                const rendered = renderMarkdown(input);
                console.log('Rendered HTML:', rendered);
                
                output.innerHTML = rendered;
                logToPage('✅ Markdown rendered successfully');
                
            } catch (error) {
                console.error('Error:', error);
                output.innerHTML = '<div style="color: #ef4444; padding: 1rem;">❌ Error: ' + error.message + '</div>';
                logToPage('❌ Error: ' + error.message, 'error');
            }
        }
        
        // Auto-test on load
        window.addEventListener('load', () => {
            setTimeout(testMarkdown, 500);
        });
    </script>
</body>
</html>