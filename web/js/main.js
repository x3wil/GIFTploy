/**
 * DATA TABLES
 */
(function($)
{
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

}(jQuery));


/**
 * Layout of commit list
 */
(function($) {
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

}(jQuery));

function setCommitButtons(row)
{
    var buttonCommitDeploy = $('#button-commit-deploy');
    var buttonCommitRollback = $('#button-commit-rollback');
    var deployUrl = $('#commit-buttons').data('deploy-url');
    var isDeployed = parseInt(row.data('commit-deployed'));

    deployUrl = deployUrl.replace('commitHash', row.data('commit-hash'));

    $('#commit-buttons a.deploy-link').attr('href', deployUrl);

    if (isDeployed === 1) {
        buttonCommitDeploy.hide();
        buttonCommitRollback.show();
    } else {
        buttonCommitDeploy.show();
        buttonCommitRollback.hide();
    }
}

function addSpinner(container)
{
    container.append('<div class="spinner"><div class="rect1"></div><div class="rect2"></div><div class="rect3"></div><div class="rect4"></div><div class="rect5"></div></div>');
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




var Ajaxa = {
	loadContent: function(url, data, container, callback, method, replace)
	{
		container = container || null;
		method = method || "GET";

		if (replace !== false)
			replace = true;

//		if (container)
//			Layout.setSpinner(container);

		var formAjaxXhr = $.ajax(
		{
			type: method,
			url: url,
			data: data,
			dataType: "json",
			async: false
		});

		formAjaxXhr.done(function(response)
		{
			if (container)
			{
				if (replace)
					container.html(response.html);
				else
					container.append(response.html);

				Layout.removeSpinner(container);
			}

			if (typeof callback === "function")
				callback(response);
		});

		formAjaxXhr.fail(function()
		{
//			alert("error");
		});

		return formAjaxXhr;
	}
};




$(document).ready(function()
{
//	$('[data-toggle=tooltip]').tooltip()
//
//	$('#tableDefaultList").DataTable({
//		"paging": false,
//		"info": false,
//		"order": [
//			[ 0, "asc" ]
//		],
//		"columnDefs": [
//			{
//				orderable: false,
//				targets: -1
//			}
//		]
//	});
//
//	formServerTestConnection();
//	formRepositoryTestConnection();

//    modal("/repository/clone/2?console=1", "Cloning repository");
});


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
	var consoleReturn = false;

	if (url.indexOf("console=1") > 0)
	{
		url = "/process-console?title="+ encodeURIComponent(title) +"&url="+ encodeURIComponent(url);
		consoleReturn = true;
	}

	var isInline = url.indexOf("#") === 0;

	if (url !== null)
	{
		var modalContent = '\
			<div class="modal fade" id="modal">\n\
				<div class="modal-dialog modal-lg">\n\
					<div class="modal-content">\n\
						<div class="modal-header">\n\
							<span class="c-ico-modal-close" data-dismiss="modal"></span>\n\
							<strong class="modal-title">'+ title +'</strong>\n\
						</div>\n\
						<div class="modal-body">\n\
							<div style="padding: 50px 0; text-align: center;">\n\
								<img src="/images/ajax_loader.gif" alt="">\n\
							</div>\n\
						</div>\n\
					</div>\n\
				</div>\n\
			</div>';


		var mo = $(modalContent);
		mo.appendTo($('body'));
		mo.one('hidden.bs.modal', function() {
			return mo.off().remove();
		});
		mo.modal();

		mo.on("hide.bs.modal", function()
		{
			var previousModal = mo.prevAll(".modal:first");

			if (previousModal.length > 0)
			{
				previousModal.show();
				previousModal.next().show();
			}
		});

		mo.on("shown.bs.modal", function()
		{
			if (consoleReturn)
			{
				$("#modal .modal-footer [data-dismiss]").addClass("disabled");
			}
		});

		if (isInline)
		{
			var inline = $(url);

			if (inline.length > 0)
			{
				return mo.find('.modal-body').html(inline.html());
			}
			else
			{
				return mo.find('.modal-body').html('Loading content failed.');
			}
		}
		else
		{
			var previousModal = mo.prevAll(".modal:first");

			if (previousModal.length > 0)
			{
				previousModal.hide();
				previousModal.next().hide();
			}

			return $.ajax({
				url: url,
				success: function(r) {
					mo.find('.modal-content').html(r);
				},
				error: function() {
					return mo.find('.modal-content').html('Načtení obsahu se nezdařilo.');
				}
			});
		}
	}
}



/*
 * LIGHTBOX
 */
(function ()
{
	$(function ()
	{
		$(document).on('click', 'a.lightbox', function (e)
		{
			e.preventDefault();

			var link = $(this);

			modal(link.attr("href"), link.data("modal-title"));
		});

	});
}).call(this);

(function ()
{
	$(function ()
	{
		$(document).on('click', 'table tr', function ()
		{
			var next = $(this).next().next();

			if (next.hasClass("commitChanges"))
			{
				next.toggleClass("hidden");
			}

		});

	});

}).call(this);



