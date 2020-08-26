
// source (eh, inspiration) inclusive-components.design/collapsible-sections/
(function() {
	var headings = document.querySelectorAll('.collapsetoggle');
	for( var i = 0; i < headings.length; i++) {
		var btn		= headings[i].querySelector('button');
		btn.onclick = function(e) {

			var expanded	= this.getAttribute('aria-expanded') === 'true' || false;
			var target		= this.parentElement.nextElementSibling;

			if ( expanded ) {
				this.setAttribute('aria-expanded', false );
				target.setAttribute('hidden', 'hidden');
			}
			else {
				this.setAttribute('aria-expanded', true );
				target.removeAttribute('hidden');
			}
		}
	}
})();
