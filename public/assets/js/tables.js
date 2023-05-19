function getObjectType(row) {
    if (row.hasOwnProperty('tags')) {
        return 'post';
    } else if (row.hasOwnProperty('content')) {
        return 'comment';
    } else if (row.hasOwnProperty('email')) {
        return 'user';
    } else {
        return 'unknown';
    }
}

function toggleCardView() {
    if (window.innerWidth < 768) {
        $('#table-user-profile-posts').bootstrapTable('refreshOptions', { cardView: true });
        $('#table-user-profile-comments').bootstrapTable('refreshOptions', { cardView: true });
        $('#table-all-comments').bootstrapTable('refreshOptions', { cardView: true });
    } else {
        $('#table-user-profile-posts').bootstrapTable('refreshOptions', { cardView: false });
        $('#table-user-profile-comments').bootstrapTable('refreshOptions', { cardView: false });
        $('#table-all-comments').bootstrapTable('refreshOptions', { cardView: false });
    }
}

function titleFormatter(value, row, index) {
    var titleLength = 15;
    var title = row.post ? row.post.title : row.title;
    var entityType = getObjectType(row);
    if (entityType === 'comment') {
        title = title.slice(0, titleLength);
    } else if (entityType === 'post') {
        title = title;
    }
    var slug = row.post ? row.post.slug : row.slug;
    var link = '<a href="/blog/post/' + slug + '">' + title + '</a>';
    return link;
}

function tagsFormatter(value, row, index) {
    var tags = row.post ? row.post.tags : row.tags;
    var tagsLength = 5;
    var tagsList = tags.slice(0, tagsLength);
    var tagsListLength = tagsList.length;
    var tagsListString = '';
    for (var i = 0; i < tagsListLength; i++) {
        tagsListString += '<span class="badge bg-info">' + tagsList[i] + '</span> ';
    }
    return tagsListString;
}

function nameFormatter(value, row, index) {
    return row.name;
}

function contentPreviewFormatter(value, row, index) {
    var previewLength = 50;
    var preview = row.content ? row.content.slice(0, previewLength) : "";
    return preview + '...';
}

function dateFormatter(value, row, index) {
    var date = new Date(row.created_at);
    return ('0' + date.getDate()).slice(-2) + '/' + (
        '0' + (
            date.getMonth() + 1
        )
    ).slice(-2) + '/' + date.getFullYear();
}

function isEnabledFormatter(value, row, index) {
    var status = row.is_enabled;
    var entityType = getObjectType(row);
    const badgeClass = status ? 'badge bg-success' : 'badge bg-warning';
    const badgeText = status ? (entityType === 'post' ? 'Publié' : 'Validé') : (entityType === 'post' ? 'Dépublié' : 'Non validé');
    return '<span class="' + badgeClass + '">' + badgeText + '</span>';
}

function userFormatter(value, row, index) {
    var entityType = getObjectType(row);
    if (entityType === 'post') {
        var user = row.author;
    } else {
        var user = row.user.username;
    }
    var userLink = '<a href="/profile/' + user + '">' + user + '</a>';
    return userLink;
}

function actionFormatter(value, row, index) {
    var actions = row.actions;
    var entityType = getObjectType(row);
    var entityId = row.id;
    var actionButtons = '';
    if (actions.voir) {
        actionButtons += '<a href="' + actions.voir + '" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i> Voir</a> ';
    }
    if (actions.editer) {
        actionButtons += '<a href="' + actions.editer + '" class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i> Editer</a> ';
    }
    if (actions.rechercher) {
        actionButtons += '<a href="' + actions.rechercher + '" class="btn btn-sm btn-info"><i class="bi bi-search"></i> Rechercher</a> ';
    }
    if (entityType === 'comment') {
        if (actions.approuver) {
            var isApproved = row.is_enabled;
            var buttonClass = isApproved ? 'btn-danger' : 'btn-success';
            var buttonText = isApproved ? 'Refuser' : 'Approuver';
            actionButtons += '<button class="btn btn-sm ' + buttonClass + '" onclick="toggleCommentApproval(this)" data-approve-url="' + actions.approuver + '" data-refuse-url="' + actions.refuser + '">' + buttonText + '</button>';
        }
    }
    if (entityType === 'post') {
        var isPublished = row.is_enabled;
        var buttonClass = isPublished ? 'btn-danger' : 'btn-success';
        var buttonText = isPublished ? 'Dépublier' : 'Publier';
        actionButtons += '<button class="btn btn-sm ' + buttonClass + '" onclick="togglePostApproval(this)" data-approve-url="' + actions.publish + '" data-refuse-url="' + actions.unpublish + '">' + buttonText + '</button>';
    }
    if (entityType === 'user') {
        var isRoleAdmin = row.roles === 'ROLE_ADMIN';
        var buttonClass = isRoleAdmin ? 'btn-danger' : 'btn-success';
        var buttonText = isRoleAdmin ? 'Rétrogader' : 'Promouvoir';
        actionButtons += '<button class="btn btn-sm ' + buttonClass + '" onclick="togglePromote(this)" data-approve-url="' + actions.promote + '" data-refuse-url="' + actions.demote + '">' + buttonText + '</button>';
    }
    return actionButtons;
}

function roleFormatter(value, row, index) {
    var role = row.roles;
    if (role === 'ROLE_ADMIN') {
        role = 'Administrateur';
        color = 'success';
    } else if (role === 'ROLE_USER') {
        role = 'Utilisateur';
        color = 'primary';
    }
    var roleBadge = '<span class="badge bg-' + color + '">' + role + '</span>';
    return roleBadge;
}

function toggleCommentApproval(buttonElement) {
    var approveUrl = buttonElement.dataset.approveUrl;
    var refuseUrl = buttonElement.dataset.refuseUrl;
    var isApproved = buttonElement.textContent.trim() === 'Refuser';
    var url = isApproved ? refuseUrl : approveUrl;
    $.ajax({
        url,
        method: 'POST',
        success: function (response) {
            if (response.success) {
                if ($('#table-all-comments').length) {
                    $('#table-all-comments').bootstrapTable('refresh');
                } else if ($('#table-user-profile-comments').length) {
                    $('#table-user-profile-comments').bootstrapTable('refresh');
                }
                var successMessage = '<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Success:"><use xlink:href="#check-circle-fill"/></svg>' + 'Le commentaire a bien été ' + (isApproved ? 'refusé' : 'approuvé');
                var errorMessage = '<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg>' + 'Le commentaire n\'a pas pu être ' + (isApproved ? 'refusé' : 'approuvé');
                if (!$('#mailerSuccess').length) {
                    $('<div id="mailerSuccess" class="alert alert-info"></div>').appendTo('#mailerSuccess');
                }
                $('#mailerSuccess').html(response.success ? successMessage : errorMessage).removeClass('invisible').show();
            }
            if (response.mailerError) {
                var mailerErrorMessage = '<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg>' + response.mailerError;
                if (!$('#mailerError').length) {
                    $('<div id="mailerError" class="alert alert-danger"></div>').appendTo('#mailerError');
                }
                $('#mailerError').html(mailerErrorMessage).removeClass('invisible').show();
            }
        },
        error: function (error) {
            console.error(error);
        }
    });
}

function togglePostApproval(buttonElement) {
    var approveUrl = buttonElement.dataset.approveUrl;
    var refuseUrl = buttonElement.dataset.refuseUrl;
    var isPublished = buttonElement.textContent.trim() === 'Dépublier';
    var url = isPublished ? refuseUrl : approveUrl;
    $.ajax({
        url,
        method: 'POST',
        success: function (response) {
            if (response.success) {
                if ($('#table-all-posts').length) {
                    $('#table-all-posts').bootstrapTable('refresh');
                } else if ($('#table-user-profile-posts').length) {
                    $('#table-user-profile-posts').bootstrapTable('refresh');
                } else if ($('#table-user-profile-posts-impersonate').length) {
                    $('#table-user-profile-posts-impersonate').bootstrapTable('refresh');
                }
                var successMessage = '<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Success:"><use xlink:href="#check-circle-fill"/></svg>' + 'L\'article a bien été ' + (isPublished ? 'dépublié' : 'publié');
                var errorMessage = '<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg>' + 'L\'article n\'a pas pu être ' + (isPublished ? 'dépublié' : 'publié');
                if (!$('#mailerSuccess').length) {
                    $('<div id="mailerSuccess" class="alert alert-info"></div>').appendTo('#mailerSuccess');
                }
                $('#mailerSuccess').html(response.success ? successMessage : errorMessage).removeClass('invisible').show();
            }
        },
        error: function (error) {
            console.error(error);
        }
    });
}

function togglePromote(buttonElement) {
    var approveUrl = buttonElement.dataset.approveUrl;
    var refuseUrl = buttonElement.dataset.refuseUrl;
    var isRoleAdmin = buttonElement.textContent.trim() === 'Rétrogader';
    var url = isRoleAdmin ? refuseUrl : approveUrl;
    var roleData = {
        role: isRoleAdmin ? 'ROLE_USER' : 'ROLE_ADMIN'
    };
    $.ajax({
        url,
        method: 'POST',
        data: roleData,
        success: function (response) {
            if (response.success) {
                $('#table-all-users').bootstrapTable('refresh');
            }
            var successMessage = '<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Success:"><use xlink:href="#check-circle-fill"/></svg>' + 'L\'utilisateur a été ' + (isRoleAdmin ? 'rétrogradé' : 'promu');
            var errorMessage = '<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg>' + 'L\'article n\'a pas pu être ' + (isRoleAdmin ? 'rétrogradé' : 'promu');
            if (!$('#mailerSuccess').length) {
                $('<div id="mailerSuccess" class="alert alert-info"></div>').appendTo('#mailerSuccess');
            }
            $('#mailerSuccess').html(response.success ? successMessage : errorMessage).removeClass('invisible').show();
        },
        error: function (error) {
            console.error(error);
        }
    });
}

function updateBootstrapTableOptions(page, limit) {
    $('#table-users').bootstrapTable('refreshOptions', {
        pageNumber: page,
        pageSize: limit
    });
}

function initBootstrapTable(selector, config) {
    var $element = $(selector);
    if ($element.length === 0) {
        console.warn("Element not found for selector:", selector);
        return;
    }
    if ($element.length > 1) {
        console.warn("Multiple elements found for selector:", selector);
        return;
    }
    $element.bootstrapTable({
        ...config,
        queryParams: function (params) {
            var currentPage = Math.floor(params.offset / params.limit) + 1;
            var pageSize = params.limit;

            return {
                limit: pageSize,
                offset: params.offset,
                page: currentPage,
            };
        },
    });
}

function generateTableConfig(url, columns, onPostBody) {
    return {
        url,
        columns,
        responseHandler: function (res) {
            return res;
        },
        pagination: true,
        sidePagination: 'server',
        pageSize: 10,
        pageList: [10, 25, 50, 100],
        search: true,
        showColumns: true,
        showRefresh: true,
        showToggle: true,
        toggle: 'table',
        toolbar: '#toolbar',
        classes: 'table table-hover table-bordered table-sm fixed-height-table table-responsive-md',
        smartDisplay: true,
        locale: 'fr-FR',
        icons: {
            refresh: 'bi bi-arrow-clockwise',
            toggle: 'bi bi-list',
            columns: 'bi bi-columns-gap',
            paginationSwitchDown: 'bi bi-chevron-down',
            paginationSwitchUp: 'bi bi-chevron-up',
        },
        onPostBody,
    };
}

$(document).ready(function () {
    if ($('#table-user-profile-posts').length) {
        initBootstrapTable('#table-user-profile-posts', generateTableConfig('/ajax/user-posts', [
            { field: 'title', title: 'Titre', formatter: titleFormatter, width: '35', widthUnit: '', widthUnit: '%' },
            { field: 'category', title: 'Categorie', width: '15', widthUnit: '%' },
            { field: 'created_at', title: 'Créé le', formatter: dateFormatter, width: '10', widthUnit: '%' },
            { field: 'tags', title: 'Tags', formatter: tagsFormatter, width: '15', widthUnit: '%' },
            { field: 'status', title: 'Statut', formatter: isEnabledFormatter, width: '10', widthUnit: '%' },
            { field: 'actions', title: 'Actions', formatter: actionFormatter, width: '15', widthUnit: '%' }
        ], function () {
            var table = $('#table-user-profile-posts');
            var page = table.bootstrapTable('getOptions').pageNumber;
            var limit = table.bootstrapTable('getOptions').pageSize;
            updateBootstrapTableOptions(page, limit);
        }));
    }
    var userId = $("#user-id").data("user-id");
    var ajaxUrl = '/ajax/user-posts';
    if ($('#table-user-profile-posts-impersonate').length) {
        ajaxUrl += '?userId=' + userId;
    }
    if ($('#table-user-profile-posts-impersonate').length) {
        initBootstrapTable('#table-user-profile-posts-impersonate', generateTableConfig(ajaxUrl, [
            { field: 'title', title: 'Titre', formatter: titleFormatter, width: '35', widthUnit: '', widthUnit: '%' },
            { field: 'category', title: 'Categorie', width: '15', widthUnit: '%' },
            { field: 'created_at', title: 'Créé le', formatter: dateFormatter, width: '10', widthUnit: '%' },
            { field: 'tags', title: 'Tags', formatter: tagsFormatter, width: '15', widthUnit: '%' },
            { field: 'status', title: 'Statut', formatter: isEnabledFormatter, width: '10', widthUnit: '%' },
        ], function () {
            var table = $('#table-user-profile-posts-impersonate');
            var page = table.bootstrapTable('getOptions').pageNumber;
            var limit = table.bootstrapTable('getOptions').pageSize;
            updateBootstrapTableOptions(page, limit);
        }));
    }

    if ($('#table-user-profile-comments').length) {
        initBootstrapTable('#table-user-profile-comments', generateTableConfig('/ajax/user-comments', [
            { field: 'post_title', title: 'Post', formatter: titleFormatter, width: '15', widthUnit: '%' },
            { field: 'content', title: 'Contenu', formatter: contentPreviewFormatter, width: '55', widthUnit: '%' },
            { field: 'created_at', title: 'Créé le', formatter: dateFormatter, width: '10', widthUnit: '%' },
            { field: 'is_enabled', title: 'Statut', formatter: isEnabledFormatter, width: '10', widthUnit: '%' },
            { field: 'actions', title: 'Actions', formatter: actionFormatter, width: '10', widthUnit: '%' }
        ], function () {
            var table = $('#table-user-profile-comments');
            var page = table.bootstrapTable('getOptions').pageNumber;
            var limit = table.bootstrapTable('getOptions').pageSize;
            updateBootstrapTableOptions(page, limit);
        }));
    }

    if ($('#table-all-comments').length) {
        initBootstrapTable('#table-all-comments', generateTableConfig('/ajax/admin-all-comments', [
            { field: 'post_title', title: 'Post', formatter: titleFormatter, width: '15', widthUnit: '%' },
            { field: 'content', title: 'Contenu', formatter: contentPreviewFormatter, width: '35', widthUnit: '%' },
            { field: 'created_at', title: 'Créé le', formatter: dateFormatter, width: '10', widthUnit: '%' },
            { field: 'is_enabled', title: 'Statut', formatter: isEnabledFormatter, width: '10', widthUnit: '%' },
            { field: 'user', title: 'Utilisateur', formatter: userFormatter, width: '10', widthUnit: '%' },
            { field: 'actions', title: 'Actions', formatter: actionFormatter, width: '20', widthUnit: '%' }
        ], function () {
            var table = $('#table-all-comments');
            var page = table.bootstrapTable('getOptions').pageNumber;
            var limit = table.bootstrapTable('getOptions').pageSize;
            updateBootstrapTableOptions(page, limit);
        }));
    }

    if ($('#table-all-tags').length) {
        initBootstrapTable('#table-all-tags', generateTableConfig('/ajax/admin-all-tags', [
            { field: 'id', title: 'ID', width: '5', widthUnit: '%' },
            { field: 'name', title: 'Nom', formatter: nameFormatter, width: '40', widthUnit: '%' },
            { field: 'slug', title: 'Slug', width: '40', widthUnit: '%' },
            { field: 'actions', title: 'Actions', formatter: actionFormatter, width: '15', widthUnit: '% ' }
        ], function () {
            var table = $('#table-all-tags');
            var page = table.bootstrapTable('getOptions').pageNumber;
            var limit = table.bootstrapTable('getOptions').pageSize;
            updateBootstrapTableOptions(page, limit);
        }));
    }

    if ($('#table-all-categories').length) {
        initBootstrapTable('#table-all-categories', generateTableConfig('/ajax/admin-all-categories', [
            { field: 'id', title: 'ID', width: '5', widthUnit: '%' },
            { field: 'name', title: 'Nom', formatter: nameFormatter, width: '15', widthUnit: '%' },
            { field: 'slug', title: 'Slug', width: '15', widthUnit: '%' },
            { field: 'actions', title: 'Actions', formatter: actionFormatter, width: '15', widthUnit: '% ' }
        ], function () {
            var table = $('#table-all-categories');
            var page = table.bootstrapTable('getOptions').pageNumber;
            var limit = table.bootstrapTable('getOptions').pageSize;
            updateBootstrapTableOptions(page, limit);
        }));
    }

    if ($('#table-all-users').length) {
        initBootstrapTable('#table-all-users', generateTableConfig('/ajax/admin-all-users', [
            { field: 'id', title: 'ID', width: '5', widthUnit: '%' },
            { field: 'username', title: 'Nom', width: '15', widthUnit: '%' },
            { field: 'email', title: 'Email', width: '15', widthUnit: '%' },
            { field: 'role', title: 'Rôle', formatter: roleFormatter, width: '10', widthUnit: '%' },
            { field: 'created_at', title: 'Créé le', formatter: dateFormatter, width: '10', widthUnit: '%' },
            { field: 'actions', title: 'Actions', formatter: actionFormatter, width: '15', widthUnit: '%' }
        ], function () {
            var table = $('#table-all-users');
            var page = table.bootstrapTable('getOptions').pageNumber;
            var limit = table.bootstrapTable('getOptions').pageSize;
            updateBootstrapTableOptions(page, limit);
        }));
    }

    if ($('#table-all-posts').length) {
        initBootstrapTable('#table-all-posts', generateTableConfig('/ajax/admin-all-posts', [
            { field: 'author', title: 'Utilisateur', formatter: userFormatter, width: '10', widthUnit: '%' },
            { field: 'title', title: 'Titre', formatter: titleFormatter, width: '15', widthUnit: '%' },
            { field: 'created_at', title: 'Créé le', formatter: dateFormatter, width: '10', widthUnit: '%' },
            { field: 'updated_at', title: 'Modifié le', formatter: dateFormatter, width: '10', widthUnit: '%' },
            { field: 'category', title: 'Catégorie', width: '5', widthUnit: '%' },
            { field: 'tags', title: 'Tags', formatter: tagsFormatter, width: '10', widthUnit: '%' },
            { field: 'is_enabled', title: 'Statut', formatter: isEnabledFormatter, width: '5', widthUnit: '%' },
            { field: 'actions', title: 'Actions', formatter: actionFormatter, width: '20', widthUnit: '%' }
        ], function () {
            var table = $('#table-all-posts');
            var page = table.bootstrapTable('getOptions').pageNumber;
            var limit = table.bootstrapTable('getOptions').pageSize;
            updateBootstrapTableOptions(page, limit);
        }));
    }

    toggleCardView();
    window.addEventListener('resize', toggleCardView);
});
