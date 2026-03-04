(function ($) {
	$(document).ready(function () {
		var similarURL  = AdminToolsIndex.tagsSimilarURL;
		var mergeURL	= AdminToolsIndex.tagsMergeURL;
		var csrfToken   = AdminToolsIndex.csrfToken;
		var pageSize	= parseInt(AdminToolsIndex.pageSize, 10) || 10;

		var allPairs	= [];
		var currentPage = 1;

		function totalPages() {
			return Math.ceil(allPairs.length / pageSize);
		}

		function renderPage(page) {
			var $results  = $('#tags-similar-results');
			var start     = (page - 1) * pageSize;
			var pagePairs = allPairs.slice(start, start + pageSize);
			var total     = allPairs.length;
			var pages     = totalPages();

			var html = '<table id="similar-tags-table">';
			html += '<thead><tr>';

			if (pages > 1) {
				html += '<th><a id="similar-tags-prev" class="button' + (page <= 1 ? ' at_disabled' : '') + '">&laquo; ' + AdminToolsIndex.prev + '</a></th>';
				html += '<th>' + AdminToolsIndex.found.replace('%d', total) + '<br><small>' + AdminToolsIndex.pageInfo.replace('%1', page).replace('%2', pages) + '</small></th>';
				html += '<th><a id="similar-tags-next" class="button' + (page >= pages ? ' at_disabled' : '') + '">' + AdminToolsIndex.next + ' &raquo;</a></th>';
			} else {
				html += '<th colspan="3">' + AdminToolsIndex.found.replace('%d', total) + '</th>';
			}

			html += '</tr></thead>';
			html += '<tbody>';
			$.each(pagePairs, function (i, pair) {
				html += '<tr>'
					+ '<td>'
					+   '<a class="button green merge-btn" '
					+     'data-source="' + pair.tag2.id + '" '
					+     'data-target="' + pair.tag1.id + '" '
					+     'data-target-name="' + pair.tag1.name + '">'
					+     AdminToolsIndex.keepLeft
					+   '</a>'
					+ '</td>'
					+ '<td>'
					+   '<span class="tag-name">' + pair.tag1.name + '</span>'
					+   ' <span class="tag-count">(' + pair.tag1.count + ')</span>'
					+   ' &harr; '
					+   '<span class="tag-name">' + pair.tag2.name + '</span>'
					+   ' <span class="tag-count">(' + pair.tag2.count + ')</span>'
					+ '</td>'
					+ '<td>'
					+   '<a class="button green merge-btn" '
					+     'data-source="' + pair.tag1.id + '" '
					+     'data-target="' + pair.tag2.id + '" '
					+     'data-target-name="' + pair.tag2.name + '">'
					+     AdminToolsIndex.keepRight
					+   '</a>'
					+ '</td>'
					+ '</tr>';
			});
			html += '</tbody></table>';
			$results.html(html);
		}

		$('#tags-find-similar').on('click', function (e) {
			e.preventDefault();
			var $btn	 = $(this);
			var $results = $('#tags-similar-results');
			$btn.prop('disabled', true).text(AdminToolsIndex.searching);
			$results.empty();

			$.get(similarURL)
			.done(function (data) {
				$btn.prop('disabled', false).text(AdminToolsIndex.findSimilar);
				if (!data.pairs.length) {
					$results.html('<p>' + AdminToolsIndex.noSimilar + '</p>');
					return;
				}
				allPairs	= data.pairs;
				currentPage = 1;
				renderPage(currentPage);
			})
			.fail(function () {
				$btn.prop('disabled', false).text(AdminToolsIndex.findSimilar);
				$results.html('<p>' + AdminToolsIndex.error + '</p>');
			});
		});

		$(document).on('click', '#similar-tags-prev', function (e) {
			e.preventDefault();
			if (currentPage > 1) {
				currentPage--;
				renderPage(currentPage);
			}
		});

		$(document).on('click', '#similar-tags-next', function (e) {
			e.preventDefault();
			if (currentPage < totalPages()) {
				currentPage++;
				renderPage(currentPage);
			}
		});

		$(document).on('click', '.merge-btn', function (e) {
			e.preventDefault();
			var $btn	   = $(this);
			var $tr		= $btn.closest('tr');
			var sourceId   = $btn.data('source');
			var targetId   = $btn.data('target');
			var targetName = $btn.data('target-name');

			if (!confirm(AdminToolsIndex.mergeConfirm.replace('%s', targetName))) return;
			$btn.prop('disabled', true);

			$.post(mergeURL, {
				source_id:  sourceId,
				target_id:  targetId,
				csrf_token: csrfToken
			})
			.done(function () {
				// Remove pair from allPairs by matching source/target ids
				allPairs = allPairs.filter(function (pair) {
					return !(
						(pair.tag1.id == sourceId && pair.tag2.id == targetId) ||
						(pair.tag2.id == sourceId && pair.tag1.id == targetId)
					);
				});

				if (allPairs.length === 0) {
					$('#tags-similar-results').html('<p>' + AdminToolsIndex.noSimilar + '</p>');
					return;
				}

				// Stay on current page, or go to previous if it's now empty
				if (currentPage > totalPages()) {
					currentPage = totalPages();
				}
				renderPage(currentPage);
			})
			.fail(function () {
				$btn.prop('disabled', false);
				alert(AdminToolsIndex.mergeError);
			});
		});
	});
})(jQuery);