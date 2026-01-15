<script>
	document.addEventListener("DOMContentLoaded", () => {
	    let wrapper = document.querySelector('.content-wrapper');
	    let section = wrapper.querySelector('div:first-of-type');
        let div = document.getElementById('activate_deactivate_btns');
        let flash = document.getElementById('flash');
        if (section && flash) {
			  // move button to preferred location and unhide
			  flash.parentNode.insertBefore(div, flash.nextSibling);
			  div.classList.remove('hidden');
        } else if (section && div) {
			  // move button to preferred location and unhide
			  wrapper.insertBefore(div, section);
			  div.classList.remove('hidden');
		} else {
			  console.warn('Activate/Deactivate Plugins: Unable to find required elements in DOM tree.');
		}
    });
</script>
