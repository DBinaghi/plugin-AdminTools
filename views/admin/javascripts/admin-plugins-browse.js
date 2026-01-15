<script>
	document.addEventListener("DOMContentLoaded", () => {
	    let wrapper = document.querySelector('.content-wrapper');
	    let section = wrapper.querySelector('div:first-of-type');
		let div = document.getElementById('activate_deactivate_btns');
		if (section && div) {
			// move button to preferred location and unhide
			wrapper.insertBefore(div, section);
			div.classList.remove('hidden');
		} else {
			console.warn('Activate/Deactivate Plugins: Unable to find required elements in DOM tree.');
		}
  });
</script>
