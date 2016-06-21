/**
 * DATA TABLES
 */
$(function() {
    window.$commitListTable = $('#commits-list table');
    window.$commitListTable.DataTable({
        'info': false,
        'paging': true,
        'pageLength': 50,
        'lengthChange': false,
        'searching': false,
        'ordering': true,
        'autoWidth': false,
        'scrollY': 200,
        'scrollCollapse': true,
        'columns': [
            { 'orderable': false },
            { 'orderable': false },
            { 'width': '5%'},
            { 'width': '5%' },
            { 'width': '3%', 'orderable': false }
        ],
        'order': [[ 2, 'desc' ]]
    });

});


/**
 * Layout of commit list
 */
$(function() {
    window.contentWrapper = $('.content-wrapper');
    window.contentHeader = $('.content-header', window.contentWrapper);
    window.content = $('.content', window.contentWrapper);

    var commitsList = $('#commits-list');
    var commitFiles = $('#commit-files');
    var commitFilesBody = $('.tab-content', commitFiles);
    var dataTableBody = $('.dataTables_scrollBody', commitsList);
    var neq = 0
    neq += $('.main-header').outerHeight();
    neq += $('.main-footer').outerHeight();
    neq += window.contentHeader.outerHeight();
    neq += (parseInt(window.content.css('padding-top')) + parseInt(window.content.css('padding-bottom')));
    neq += (commitsList.height() - dataTableBody.height());

    // Sets table of commits to full window height.
    var setBoxHeight = function() {
        dataTableBody.height($(window).height() - neq);
        commitFilesBody.height(commitsList.height() -64);
    };

    setBoxHeight();

    // Set height on window resize.
    $(window).resize(function() {
        setBoxHeight();
    });

    var tableRows = $('tbody tr', commitsList);
    var actualCommit = null;
    var actualCommitList = null;
    var actualDiffList = null;
    var actualDiffDeployList = null;

    // Set height on datatable redraw.
    window.$commitListTable.on('draw.dt', function () {
        setBoxHeight();
        tableRows = $('tbody tr', commitsList);
    });

    commitsList.on('click', 'tbody tr', function() {

        var row = $(this);

        if (!row.hasClass('info')) {
            tableRows.removeClass('info');
            row.addClass('info');

            if (actualCommitList !== null) {
                actualCommitList.hide();
            }

            actualCommit = row.data('commit-hash');
            actualCommitList = $('#commit-' + actualCommit).show();
            $('#tab-folder-commit-files').tab('show');

            setCommitButtons(row);
        }

    });

    tableRows.first().trigger('click');

    // Showing diff in tab
    $('a[data-toggle="tab"]', commitFiles).on('shown.bs.tab', function (e) {
        if ($(e.target).hasClass('load-diff')) {

            var container = null;

            if (e.target.href.indexOf('#tab-diff-deploy') !== -1) { // Files diff to last deploy
                if (actualDiffDeployList !== null) {
                    actualDiffDeployList.hide();
                }

                container = actualDiffDeployList = $('#diff-'+ actualCommit +'-deploy').show();

            } else {
                if (actualDiffList !== null) {
                    actualDiffList.hide();
                }

                container = actualDiffList = $('#diff-' + actualCommit).show();
            }

            if (container.html() === '') {
                var commitHashFrom =  container.data('commit-from');
                var commitHashTo =  container.data('commit-to');

                addSpinner(container);

                var ajax = new Ajax();
                ajax.loadContent('/project/show-diff/'+ commitsList.data('environment-id') +'/'+ commitHashFrom +'/'+ commitHashTo, [], container);
            }
        }
    });

});

function setCommitButtons(row)
{
    var $commitButtons = $('#commit-buttons');
    var buttonCommitDeploy = $('#button-commit-deploy');
    var buttonCommitRollback = $('#button-commit-rollback');
    var deployUrl = $commitButtons.data('deploy-url');
    var markUrl = $commitButtons.data('mark-url');
    var isDeployed = parseInt(row.data('commit-deployed'));

    deployUrl = deployUrl.replace('commitHash', row.data('commit-hash'));
    markUrl = markUrl.replace('commitHash', row.data('commit-hash'));

    $('a.deploy-link', $commitButtons).attr('href', deployUrl).data('modal-title', 'Deploying ' + row.data('commit-hash'));
    $('a.mark-link', $commitButtons).attr('href', markUrl);

    if (isDeployed === 1) {
        buttonCommitDeploy.hide();
        buttonCommitRollback.show();
    } else {
        buttonCommitDeploy.show();
        buttonCommitRollback.hide();
    }
}

function getSpinner()
{
    return $('<div class="spinner"><div class="rect1"></div><div class="rect2"></div><div class="rect3"></div><div class="rect4"></div><div class="rect5"></div></div>');
}

function addSpinner(container)
{
    container.append(getSpinner());
}

function Ajax()
{
    this.xhr = null;

    this.abort = function()
    {
        if (this.xhr !== null) {
            this.xhr.abort();
        }

        this.xhr = null;
    };

    this.loadContent = function(url, data, container, callback, method, replace)
	{
		container = container || null;
		method = method || "GET";

		if (replace !== false)
			replace = true;

		this.xhr = $.ajax(
		{
			type: method,
			url: url,
			data: data,
			dataType: "json",
			async: false
		});

		this.xhr.done(function(response)
		{
			if (container)
			{
				if (replace)
					container.html(response.html);
				else
					container.append(response.html);
			}

			if (typeof callback === "function")
				callback(response);
		});

		this.xhr.fail(function()
		{
			alert("error");
		});

		this.abort();
    };
}




function formRepositoryTestConnection()
{
	var formRepositoryHolder = $("#formRepository");

	if (formRepositoryHolder.length > 0)
	{
		var xhr = null;
		var saveFormButton = $("#saveForm");
		var button = $("#testConnection");
		var branchSelect = $("#branch");

		if (formRepositoryHolder.hasClass("new"))
		{
			saveFormButton.attr("disabled", true);
		}

		button.click(function()
		{
			if (xhr !== null)
			{
				return;
			}

			if (formRepositoryHolder.hasClass("new"))
			{
				saveFormButton.attr("disabled", true);
				branchSelect.find("option").remove().end().append('<option value="0">- select -</option>');
			}

			button.removeClass("btn-danger");
			button.removeClass("btn-success");
			button.addClass("btn-info");
			button.html("Attempt to connect...");

			xhr = $.ajax({
				method: "POST",
				url: "/Repository/testConnection",
				data: { remote: $("#remote").val(), username: $("#username").val(), pass: $("#pass").val() }
			})
			.done(function(response)
			{
				if (response.branches.length > 0)
				{
					button.removeClass("btn-danger");
					button.removeClass("btn-info");
					button.addClass("btn-success");
					button.html("Connection OK, select branch");

					saveFormButton.attr("disabled", false);

					branchSelect.find("option").remove().end().append('<option value="0">- select -</option>');

					for (var i = 0; i < response.branches.length; i++)
					{
						branchSelect.append($("<option />").val(response.branches[i]).text(response.branches[i]));
					}
				}
				else
				{
					button.removeClass("btn-success");
					button.removeClass("btn-info");
					button.addClass("btn-danger");
					button.html("Connection failed");
				}
			})
			.fail(function()
			{
				button.html("Unknown error, try again");
			})
			.always(function() {
				xhr = null;
			});
		});

		$("#remote, #username, #pass").keyup(function()
		{
			if (xhr !== null)
			{
				xhr.abort();
				xhr = null;
			}

			button.removeClass("btn-danger");
			button.removeClass("btn-success");
			button.addClass("btn-info");
			button.html("Test connection");

			saveFormButton.attr("disabled", true);

			branchSelect.find("option").remove().end().append('<option value="0">- select -</option>');
		});
	}
}


function formServerTestConnection()
{
	var formServerHolder = $("#formServer");

	if (formServerHolder.length > 0)
	{
		var xhr = null;
		var saveFormButton = $("#saveForm");
		var button = $("#testConnection");

		if (formServerHolder.hasClass("new"))
		{
			saveFormButton.attr("disabled", true);
		}

		button.click(function()
		{
			if (xhr !== null)
			{
				return;
			}

			if (formServerHolder.hasClass("new"))
			{
				saveFormButton.attr("disabled", true);
			}

			button.removeClass("btn-danger");
			button.removeClass("btn-success");
			button.addClass("btn-info");
			button.html("Attempt to connect...");

			xhr = $.ajax({
				method: "POST",
				url: "/Server/testConnection",
				data: { host: $("#host").val(), user: $("#user").val(), pass: $("#pass").val() }
			})
			.done(function(response)
			{
				if (response.result === true)
				{
					button.removeClass("btn-danger");
					button.removeClass("btn-info");
					button.addClass("btn-success");

					button.html("Connection OK");

					saveFormButton.attr("disabled", false);
				}
				else
				{
					button.removeClass("btn-success");
					button.removeClass("btn-info");
					button.addClass("btn-danger");

					button.html(response.error);
				}
			})
			.fail(function()
			{
				button.html("Unknown error, try again");
			})
			.always(function() {
				xhr = null;
			});
		});

		$("#host, #user, #pass").keyup(function()
		{
			if (xhr !== null)
			{
				xhr.abort();
				xhr = null;
			}

			button.removeClass("btn-danger");
			button.removeClass("btn-success");
			button.addClass("btn-info");
			button.html("Test connection");

			saveFormButton.attr("disabled", true);
		});
	}
}

function modal(url, title)
{
	var isProcess = false;

    if (url.indexOf("/process/") !== -1) {
        url = "/process-console?title="+ encodeURIComponent(title) +"&url="+ encodeURIComponent(url);
		isProcess = true;
    }

    var modalContent = '' +
        '<div class="modal fade" tabindex="-1" role="dialog">' +
            '<div class="modal-dialog modal-lg">' +
                '<div class="modal-content">' +
                    '<div class="modal-body">' + getSpinner()[0].outerHTML + '</div>' +
                '</div>' +
            '</div>' +
        '</div>';

    var mo = $(modalContent);
    mo.appendTo($('body'));
    mo.one('hidden.bs.modal', function() {
		if (isProcess) {
			window.location.reload();
		}

        return mo.off().remove();
    });
    mo.modal({
        backdrop: 'static',
        keyboard: false
    });

    return $.ajax({
        url: url,
        success: function(r) {
            mo.find('.modal-content').html(r);
        },
        error: function() {
            return mo.find('.modal-body').html('Loading failed');
        }
    });
}

$(function () {

	$(document).on('click', 'a.console', function (e) {
		e.preventDefault();
		var link = $(this);
		modal(link.attr("href"), link.data("modal-title"));
	});

	$(document).on('click', 'table tr', function () {
		var next = $(this).next().next();

		if (next.hasClass("commitChanges")) {
			next.toggleClass("hidden");
		}
	});
});



