<script>
	document.addEventListener("DOMContentLoaded", () => {
		let section = document.querySelector('.content-wrapper section:first-of-type');
		let form = document.querySelector('form.det');
		if(section && form){
			// move button to preferred location and unhide
			section.appendChild(form);
			form.classList.remove('hidden');
		}else{
			console.warn('Delete Empty Tags: Unable to find required elements in DOM tree.');
		}
	});
</script>