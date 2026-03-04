document.addEventListener("DOMContentLoaded", () => {
	let section = document.querySelector('.content-wrapper section:first-of-type');
	let form = document.querySelector('.at_form');

	if (section && form) {
		// move button to preferred location and unhide
		section.appendChild(form);
		form.classList.remove('hidden');
	} else {
		console.warn('Delete Empty Tags: Unable to find required elements in DOM tree.');
	}
});

(function ($) {
    $(document).ready(function () {
        var $editTags = $('.edit-tag');
        if (!$editTags.length) return;

        // Destroy X-editable instances initialized by core's tags.js
        $editTags.editable('destroy');

        var renameTagURL = window.AdminTools.renameTagURL;
        var mergeTagsURL = window.AdminTools.mergeTagsURL;
        var tagURLBase   = window.AdminTools.tagURLBase;
        var csrfToken    = window.AdminTools.csrfToken;

        $editTags.editable({
            url: renameTagURL,
            mode: 'inline',
            type: 'text',
            showbuttons: false,
            params: function (params) {
                params.csrf_token = csrfToken;
                return params;
            },
            success: function (response, newValue) {
                if (response && response.duplicate) {
                    var $tag      = $(this);
                    var $li       = $tag.parents('li');
                    var sourceId  = $tag.data('pk');
                    var targetId  = response.target_id;

                    if (confirm(response.message + '\n\n' + window.AdminTools.mergeConfirm)) {
                        $.post(mergeTagsURL, {
                            source_id:  sourceId,
                            target_id:  targetId,
                            csrf_token: csrfToken
                        })
						.done(function (response) {
							// Update target tag count only if it's in the current page
							var $targetLi    = $('.edit-tag[data-pk="' + targetId + '"]').parents('li');
							if ($targetLi.length) {
								$targetLi.find('.count').text(response.count);
							}

							// Remove source tag row
							$li.fadeOut(300, function () { $li.remove(); });
						})
						.fail(function () {
                            alert(window.AdminTools.mergeError);
                        });
                    }
                    // Prevent X-editable from updating the displayed value
                    return false;
                }
                // Normal rename: update the browse link
                $(this).parents('li').find('a.count').first().attr('href', tagURLBase + newValue);
            },
            error: function (response) {
                return response.responseText || window.AdminTools.renameError;
            }
        });
    });
})(jQuery);
