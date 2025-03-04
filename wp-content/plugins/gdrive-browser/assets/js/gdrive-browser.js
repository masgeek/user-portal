jQuery(document).ready(function ($) {
    // Utility object with file-related functions
    const GDriveUtils = {
        // File type mappings
        fileTypes: {
            'application/vnd.google-apps.folder': 'folder',
            'application/vnd.google-apps.document': 'document',
            'application/vnd.google-apps.spreadsheet': 'spreadsheet',
            'application/vnd.google-apps.presentation': 'presentation',
            'application/pdf': 'pdf',
            'image/jpeg': 'image',
            'image/png': 'image',
            'image/gif': 'image',
            'text/plain': 'text',
            'text/csv': 'spreadsheet',
            'application/zip': 'archive',
            'video/mp4': 'video',
            'audio/mpeg': 'audio'
        },

        // Icon classes
        iconClasses: {
            'folder': 'fas fa-folder',
            'document': 'fas fa-file-alt',
            'spreadsheet': 'fas fa-file-excel',
            'presentation': 'fas fa-file-powerpoint',
            'pdf': 'fas fa-file-pdf',
            'image': 'fas fa-file-image',
            'text': 'fas fa-file-alt',
            'archive': 'fas fa-file-archive',
            'video': 'fas fa-file-video',
            'audio': 'fas fa-file-audio',
            'generic': 'fas fa-file'
        },

        // Format file size
        formatFileSize: function (bytes) {
            if (bytes === 0 || bytes === null || bytes === undefined) {
                return '-';
            }

            const units = ['B', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(1024));

            return (bytes / Math.pow(1024, i)).toFixed(2) + ' ' + units[i];
        },

        // Format date
        formatDate: function (dateString) {
            if (!dateString) {
                return '-';
            }

            const date = new Date(dateString);
            return date.toLocaleDateString();
        },

        // Get file type from MIME type
        getFileType: function (mimeType) {
            return this.fileTypes[mimeType] || 'generic';
        },

        // Get file icon class based on file type
        getFileIconClass: function (fileType) {
            return this.iconClasses[fileType] || this.iconClasses['generic'];
        },

        // Sort files - folders first, then alphabetically
        sortFiles: function (files) {
            return files.sort(function (a, b) {
                const aIsFolder = a.mimeType === 'application/vnd.google-apps.folder';
                const bIsFolder = b.mimeType === 'application/vnd.google-apps.folder';

                if (aIsFolder && !bIsFolder) return -1;
                if (!aIsFolder && bIsFolder) return 1;

                return a.name.localeCompare(b.name);
            });
        }
    };

    // Browser UI object - handles rendering and interactivity
    const GDriveBrowser = {
        // Initialize
        init: function () {
            this.bindEvents();
        },

        // Bind events
        bindEvents: function () {
            $(document).on('click', '.gdrive-folder-link, .gdrive-open-folder', this.handleFolderClick);
            $(document).on('click', '.gdrive-breadcrumb-link', this.handleBreadcrumbClick);
            $(document).on('input', '.gdrive-search-input', this.handleSearch);
        },

        // Handle folder click
        handleFolderClick: function (e) {
            e.preventDefault();

            const container = $(this).closest('.gdrive-browser-container');
            const folderId = $(this).data('folder-id');

            GDriveBrowser.changeFolder(container, folderId);
        },

        // Handle breadcrumb click
        handleBreadcrumbClick: function (e) {
            e.preventDefault();

            const container = $(this).closest('.gdrive-browser-container');
            const folderId = $(this).data('folder-id');

            GDriveBrowser.changeFolder(container, folderId);
        },

        // Handle search input
        handleSearch: function () {
            const container = $(this).closest('.gdrive-browser-container');
            const searchTerm = $(this).val().toLowerCase();

            if (searchTerm.length === 0) {
                // Show all items if search is cleared
                container.find('.gdrive-file-row').show();
                return;
            }

            // Filter items based on search term
            container.find('.gdrive-file-row').each(function () {
                const fileName = $(this).find('.gdrive-file-name').text().toLowerCase();
                $(this).toggle(fileName.includes(searchTerm));
            });
        },

        // Change folder
        changeFolder: function (container, folderId) {
            // Show loading indicator
            this.showLoading(container, true);

            // Clear search input
            container.find('.gdrive-search-input').val('');

            // Get root folder and restriction settings
            const rootFolderId = container.data('root-folder');
            const restrictFolder = container.data('restrict-folder') === 1;

            // Make AJAX request
            $.ajax({
                url: gdriveData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'gdrive_change_folder',
                    nonce: gdriveData.nonce,
                    folder_id: folderId,
                    root_folder_id: rootFolderId,
                    restrict_to_folder: restrictFolder
                },
                success: function (response) {
                    if (response.success) {
                        // Update current folder
                        container.attr('data-current-folder', response.data.current_folder);

                        // Update breadcrumbs
                        GDriveBrowser.updateBreadcrumbs(container, response.data.breadcrumbs, rootFolderId);

                        // Update files list
                        GDriveBrowser.updateFilesList(container, response.data.files);
                    } else {
                        GDriveBrowser.showError(container, response.data.message);
                    }
                },
                error: function () {
                    GDriveBrowser.showError(container, 'Error connecting to server');
                },
                complete: function () {
                    // Hide loading indicator
                    GDriveBrowser.showLoading(container, false);
                }
            });
        },

        // Show/hide loading indicator
        showLoading: function (container, show) {
            if (show) {
                container.find('.gdrive-loading').addClass('active');
                container.find('.gdrive-files-container').addClass('loading');
            } else {
                container.find('.gdrive-loading').removeClass('active');
                container.find('.gdrive-files-container').removeClass('loading');
            }
        },

        // Update breadcrumbs
        updateBreadcrumbs: function (container, breadcrumbs, rootFolderId) {
            const breadcrumbsContainer = container.find('.gdrive-breadcrumbs');

            if (breadcrumbsContainer.length === 0 || !breadcrumbs) {
                return;
            }

            breadcrumbsContainer.empty();

            // Find index of root folder in breadcrumbs if it exists
            let rootIndex = -1;
            if (rootFolderId) {
                $.each(breadcrumbs, function (index, crumb) {
                    if (crumb.id === rootFolderId) {
                        rootIndex = index;
                        return false; // Break the loop
                    }
                });
            }

            // If root folder is in breadcrumbs, only show from that point forward
            const startIndex = rootIndex > -1 ? rootIndex : 0;
            const last = breadcrumbs.length - 1;

            for (let i = startIndex; i <= last; i++) {
                const crumb = breadcrumbs[i];

                if (i === last) {
                    breadcrumbsContainer.append(
                        $('<span>')
                            .addClass('gdrive-breadcrumb-current')
                            .text(crumb.name)
                    );
                } else {
                    breadcrumbsContainer.append(
                        $('<a>')
                            .attr('href', '#')
                            .addClass('gdrive-breadcrumb-link')
                            .attr('data-folder-id', crumb.id)
                            .text(crumb.name)
                    );

                    breadcrumbsContainer.append(
                        $('<span>')
                            .addClass('gdrive-breadcrumb-separator')
                            .text('/')
                    );
                }
            }
        },

        // Render empty folder message
        renderEmptyFolder: function () {
            return $('<div>')
                .addClass('gdrive-empty-folder')
                .append($('<i>').addClass('fas fa-folder-open'))
                .append($('<p>').text('No files found in this folder.'));
        },

        // Create table structure
        createTableStructure: function () {
            const table = $('<table>').addClass('gdrive-files-table');

            // Header row
            const thead = $('<thead>').append(
                $('<tr>')
                    .append($('<th>').addClass('gdrive-col-name').text('Name'))
                    .append($('<th>').addClass('gdrive-col-size').text('Size'))
                    .append($('<th>').addClass('gdrive-col-modified').text('Modified'))
                    .append($('<th>').addClass('gdrive-col-actions').text('Actions'))
            );

            // Table body
            const tbody = $('<tbody>');

            return table.append(thead).append(tbody);
        },

        // Create file row
        createFileRow: function (file) {
            const isFolder = file.mimeType === 'application/vnd.google-apps.folder';
            const fileType = GDriveUtils.getFileType(file.mimeType);
            const iconClass = GDriveUtils.getFileIconClass(fileType);

            // Create row
            const row = $('<tr>').addClass('gdrive-file-row ' + (isFolder ? 'gdrive-folder-row' : 'gdrive-document-row'));

            // Name cell
            const nameCell = $('<td>').addClass('gdrive-col-name');

            if (isFolder) {
                nameCell.append(
                    $('<a>')
                        .attr('href', '#')
                        .addClass('gdrive-folder-link')
                        .attr('data-folder-id', file.id)
                        .append($('<span>').addClass('gdrive-icon ' + iconClass))
                        .append($('<span>').addClass('gdrive-file-name').text(file.name))
                );
            } else {
                nameCell.append(
                    $('<a>')
                        .attr('href', file.webViewLink)
                        .attr('target', '_blank')
                        .addClass('gdrive-file-link')
                        .append($('<span>').addClass('gdrive-icon ' + iconClass))
                        .append($('<span>').addClass('gdrive-file-name').text(file.name))
                );
            }

            // Size cell
            const sizeCell = $('<td>')
                .addClass('gdrive-col-size')
                .text(isFolder ? '-' : GDriveUtils.formatFileSize(file.size));

            // Modified cell
            const modifiedCell = $('<td>')
                .addClass('gdrive-col-modified')
                .text(GDriveUtils.formatDate(file.modifiedTime));

            // Actions cell
            const actionsCell = $('<td>').addClass('gdrive-col-actions');

            if (isFolder) {
                actionsCell.append(
                    $('<a>')
                        .attr('href', '#')
                        .addClass('gdrive-action-button gdrive-open-folder')
                        .attr('data-folder-id', file.id)
                        .attr('title', 'Open folder')
                );
            } else {
                actionsCell.append(
                    $('<a>')
                        .attr('href', file.webViewLink)
                        .attr('target', '_blank')
                        .addClass('gdrive-action-button gdrive-view-file')
                        .attr('title', 'View file')
                );
            }

            // Add cells to row
            return row.append(nameCell).append(sizeCell).append(modifiedCell).append(actionsCell);
        },

        // Update files list
        updateFilesList: function (container, files) {
            const filesContainer = container.find('.gdrive-files-container');

            if (!files || files.length === 0) {
                filesContainer.html(this.renderEmptyFolder());
                return;
            }

            // Sort files
            const sortedFiles = GDriveUtils.sortFiles(files);

            // Create table
            const table = this.createTableStructure();
            const tbody = table.find('tbody');

            // Add files to table
            $.each(sortedFiles, function (index, file) {
                tbody.append(GDriveBrowser.createFileRow(file));
            });

            // Update container
            filesContainer.empty().append(table);
        },

        // Show error message
        showError: function (container, message) {
            container.find('.gdrive-files-container').html(
                $('<div>')
                    .addClass('gdrive-error')
                    .text(message)
            );
        }
    };

    // Initialize browser
    GDriveBrowser.init();
});