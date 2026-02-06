<script>
	document.addEventListener("DOMContentLoaded", () => {
		let el_tag_search = document.getElementById('tag-search');
		let el_has_tags = document.getElementById('has-tags');
		if (el_tag_search && el_has_tags) {
			let target = el_has_tags.parentNode.parentNode;
			// move target to preferred location and unhide
			el_tag_search.parentNode.parentNode.insertBefore(target, el_tag_search.parentNode.nextSibling)
			target.classList.remove('hidden');
		} else {
			console.warn('Has Tags: Unable to find required elements in DOM tree.');
		}
	});
</script>