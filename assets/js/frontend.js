let myLabels = document.querySelectorAll('.series-toggle-label');

Array.from(myLabels).forEach((label) => {
	label.addEventListener('keydown', (e) => {
		// 32 === spacebar
		// 13 === enter
		if (e.which === 32 || e.which === 13) {
			e.preventDefault();
			label.click();
		}
	});
});
