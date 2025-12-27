<?php
/**
 * S3 Test Page
 *
 * Provides interface for testing S3 file upload functionality.
 * All AWS operations are handled server-side for security.
 */
$this->assign('title', 'S3 File Upload Test');
?>

<div class="s3-test-container">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">S3 File Upload Test</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Configuration:</strong><br>
                        <small>
                            <strong>Bucket:</strong> <?= h($config['bucket_name']) ?><br>
                            <strong>Region:</strong> <?= h($config['region']) ?><br>
                            <strong>Upload Method:</strong> Server-side (secure)
                        </small>
                    </div>

                    <form id="uploadForm" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="fileInput" class="form-label">Select File to Upload:</label>
                            <input type="file" id="fileInput" name="file" class="form-control" accept="*/*">
                        </div>

                        <div class="btn-group mb-3" role="group">
                            <button type="submit" id="uploadBtn" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Upload File
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="S3Test.clearLog()">
                                <i class="fas fa-trash"></i> Clear Log
                            </button>
                        </div>
                    </form>

                    <div class="progress mb-3" id="progressContainer" style="display: none;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                             id="progressBar"
                             role="progressbar"
                             style="width: 0%">0%</div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Uploaded Files</h5>
                        </div>
                        <div class="card-body">
                            <div id="uploadedFiles" class="uploaded-files-container">
                                <p class="text-muted">No files uploaded yet.</p>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Upload Log</h5>
                        </div>
                        <div class="card-body">
                            <div id="log" class="log-container"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.s3-test-container {
    margin: 20px 0;
}
.log-container {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    padding: 15px;
    height: 400px;
    overflow-y: auto;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    white-space: pre-wrap;
}
.log-entry {
    margin-bottom: 5px;
}
.log-success { color: #28a745; }
.log-error { color: #dc3545; }
.log-info { color: #007bff; }
.uploaded-files-container {
    min-height: 100px;
}
.file-item {
    border: 1px solid #e9ecef;
    border-radius: 5px;
    padding: 15px;
    margin-bottom: 10px;
    background-color: #f8f9fa;
}
.file-item:last-child {
    margin-bottom: 0;
}
.file-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}
.file-details {
    flex-grow: 1;
}
.file-name {
    font-weight: bold;
    color: #495057;
}
.file-meta {
    font-size: 0.85em;
    color: #6c757d;
    margin-top: 5px;
}
.file-actions {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}
.file-actions .btn {
    padding: 5px 15px;
    font-size: 0.85em;
}
</style>

<script>
const S3Test = (function() {
    'use strict';

    const DOM = {
        logElement: null,
        progressContainer: null,
        progressBar: null,
        uploadedFilesElement: null,
        uploadForm: null,
        fileInput: null,
        uploadBtn: null
    };

    const state = {
        uploadedFiles: [],
        csrfToken: '<?= $this->request->getAttribute('csrfToken') ?>'
    };

    function initializeDOM() {
        DOM.logElement = document.getElementById('log');
        DOM.progressContainer = document.getElementById('progressContainer');
        DOM.progressBar = document.getElementById('progressBar');
        DOM.uploadedFilesElement = document.getElementById('uploadedFiles');
        DOM.uploadForm = document.getElementById('uploadForm');
        DOM.fileInput = document.getElementById('fileInput');
        DOM.uploadBtn = document.getElementById('uploadBtn');
    }

    function log(message, type = 'info') {
        const timestamp = new Date().toLocaleTimeString();
        const logEntry = document.createElement('div');
        logEntry.className = `log-entry log-${type}`;
        logEntry.innerHTML = `<strong>[${timestamp}]</strong> ${message}`;
        DOM.logElement.appendChild(logEntry);
        DOM.logElement.scrollTop = DOM.logElement.scrollHeight;
    }

    function updateProgress(percentage) {
        DOM.progressContainer.style.display = 'block';
        DOM.progressBar.style.width = percentage + '%';
        DOM.progressBar.textContent = percentage + '%';
    }

    function hideProgress() {
        DOM.progressContainer.style.display = 'none';
        DOM.progressBar.style.width = '0%';
        DOM.progressBar.textContent = '0%';
    }

    function formatFileSize(bytes) {
        return (bytes / 1024).toFixed(2) + ' KB';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function createFileListItem(file) {
        return `
            <div class="file-item">
                <div class="file-info">
                    <div class="file-details">
                        <div class="file-name">${escapeHtml(file.name)}</div>
                        <div class="file-meta">
                            <strong>Size:</strong> ${file.sizeFormatted} |
                            <strong>Key:</strong> ${escapeHtml(file.key)} |
                            <strong>Uploaded:</strong> ${escapeHtml(file.uploadTime)}
                        </div>
                    </div>
                </div>
                <div class="file-actions">
                    <button class="btn btn-success btn-sm" onclick="S3Test.downloadFile('${escapeHtml(file.key)}', '${escapeHtml(file.name)}')">
                        <i class="fas fa-download"></i> Download
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="S3Test.deleteFile('${escapeHtml(file.key)}')">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        `;
    }

    function updateFileList() {
        if (state.uploadedFiles.length === 0) {
            DOM.uploadedFilesElement.innerHTML = '<p class="text-muted">No files uploaded yet.</p>';
            return;
        }

        DOM.uploadedFilesElement.innerHTML = state.uploadedFiles
            .map(file => createFileListItem(file))
            .join('');
    }

    function addUploadedFile(fileInfo) {
        state.uploadedFiles.push(fileInfo);
        updateFileList();
    }

    function removeUploadedFile(key) {
        state.uploadedFiles = state.uploadedFiles.filter(file => file.key !== key);
        updateFileList();
    }

    function setUploadButtonState(isUploading) {
        DOM.uploadBtn.disabled = isUploading;
        DOM.uploadBtn.innerHTML = isUploading
            ? '<i class="fas fa-spinner fa-spin"></i> Uploading...'
            : '<i class="fas fa-upload"></i> Upload File';
    }

    function handleUploadProgress(loaded, total) {
        const percentage = Math.round((loaded / total) * 100);
        updateProgress(percentage);
        log(`Upload progress: ${percentage}% (${formatFileSize(loaded)}/${formatFileSize(total)})`, 'info');
    }

    function handleUploadSuccess(response, file) {
        log('✓ Upload successful!', 'success');
        log(`File location: ${response.data.key}`, 'info');
        log(`ETag: ${response.data.etag}`, 'info');

        addUploadedFile({
            key: response.data.key,
            name: response.data.name,
            size: response.data.size,
            sizeFormatted: formatFileSize(response.data.size),
            uploadTime: response.data.uploadTime || new Date().toLocaleString(),
            etag: response.data.etag
        });

        log('File added to uploaded files list', 'success');
        DOM.fileInput.value = '';
    }

    function handleUploadError(message) {
        log(`✗ Upload failed: ${message}`, 'error');
    }

    function uploadFile(file) {
        const fileName = file.name;
        log(`Starting upload: ${fileName} (${formatFileSize(file.size)})`, 'info');

        setUploadButtonState(true);
        updateProgress(0);

        const formData = new FormData();
        formData.append('file', file);

        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                handleUploadProgress(e.loaded, e.total);
            }
        });

        xhr.addEventListener('load', () => {
            setUploadButtonState(false);
            hideProgress();

            if (xhr.status === 200 && xhr.responseText) {
                try {
                    const result = JSON.parse(xhr.responseText);
                    if (result.response.success) {
                        handleUploadSuccess(result.response, file);
                    } else {
                        handleUploadError(result.response.message);
                    }
                } catch (e) {
                    handleUploadError('Invalid server response');
                }
            } else {
                handleUploadError(`Server returned status ${xhr.status}`);
            }
        });

        xhr.addEventListener('error', () => {
            setUploadButtonState(false);
            hideProgress();
            handleUploadError('Network error');
        });

        xhr.open('POST', '/s3-test/upload');
        xhr.setRequestHeader('X-CSRF-Token', state.csrfToken);
        xhr.send(formData);
    }

    function convertBase64ToBlob(base64, contentType) {
        const byteCharacters = atob(base64);
        const byteNumbers = new Array(byteCharacters.length);

        for (let i = 0; i < byteCharacters.length; i++) {
            byteNumbers[i] = byteCharacters.charCodeAt(i);
        }

        return new Blob([new Uint8Array(byteNumbers)], { type: contentType });
    }

    function triggerFileDownload(blob, filename) {
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
    }

    function setupEventListeners() {
        DOM.uploadForm.addEventListener('submit', (e) => {
            e.preventDefault();

            if (!DOM.fileInput.files.length) {
                log('Please select a file first.', 'error');
                return;
            }

            uploadFile(DOM.fileInput.files[0]);
        });
    }

    function initialize() {
        initializeDOM();
        setupEventListeners();

        log('S3 Upload Test Page loaded', 'info');
        log('Bucket: <?= h($config['bucket_name']) ?>', 'info');
        log('🔒 Secure mode: AWS credentials are server-side only', 'success');
    }

    return {
        initialize,

        clearLog() {
            DOM.logElement.innerHTML = '';
            hideProgress();
        },

        downloadFile(key, fileName) {
            log(`Starting download: ${fileName}`, 'info');

            fetch('/s3-test/download', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': state.csrfToken
                },
                body: JSON.stringify({ key })
            })
            .then(response => response.json())
            .then(data => {
                if (data.response.success) {
                    log(`✓ Download successful! File size: ${formatFileSize(data.response.data.size)}`, 'success');

                    const blob = convertBase64ToBlob(data.response.data.content, data.response.data.contentType);
                    triggerFileDownload(blob, fileName);

                    log(`File downloaded: ${fileName}`, 'success');
                } else {
                    log(`✗ Download failed: ${data.response.message}`, 'error');
                }
            })
            .catch(error => {
                log(`✗ Download failed: ${error.message}`, 'error');
            });
        },

        deleteFile(key) {
            const file = state.uploadedFiles.find(f => f.key === key);
            const fileName = file ? file.name : key;

            if (!confirm(`Are you sure you want to delete "${fileName}"?`)) {
                return;
            }

            log(`Deleting file: ${fileName}`, 'info');

            fetch('/s3-test/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': state.csrfToken
                },
                body: JSON.stringify({ key })
            })
            .then(response => response.json())
            .then(data => {
                if (data.response.success) {
                    log(`✓ Delete successful! File removed from S3: ${fileName}`, 'success');
                    removeUploadedFile(key);
                } else {
                    log(`✗ Delete failed: ${data.response.message}`, 'error');
                }
            })
            .catch(error => {
                log(`✗ Delete failed: ${error.message}`, 'error');
            });
        }
    };
})();

document.addEventListener('DOMContentLoaded', S3Test.initialize);
</script>