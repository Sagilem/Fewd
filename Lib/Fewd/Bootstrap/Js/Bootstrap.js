//----------------------------------------------------------------------------------------------------------------------
//
// FEWD - Just a FEW Development (https://fewd.org)
//
//----------------------------------------------------------------------------------------------------------------------


//----------------------------------------------------------------------------------------------------------------------
// Drag resize for sidebars
//----------------------------------------------------------------------------------------------------------------------
$(document).ready(function()
{
	var fewd_draggedObject = null;

	$('.fewd-drag').mousedown(function(e)
	{
		fewd_draggedObject = $(this).parent();
	});

	$(window).mousemove(function(e)
	{
		if(fewd_draggedObject != null)
		{
			fewd_draggedObject.width(e.clientX);
		}
	});

	$(window).mouseup(function()
	{
		fewd_draggedObject = null;
	});
});


//------------------------------------------------------------------------------------------------------------------
// Component resize queries : components that can change their class depending on their size
//------------------------------------------------------------------------------------------------------------------
function fewd_resizeQuery(selector, breakpoints)
{
	// If "ResizeObserver" is not managed by the browser :
	// Does nothing
	if(!('ResizeObserver' in self))
	{
		return;
	}

	// If not already created :
	// Creates a global resize observer, that will apply resize breakpoints
	// on components having a "data-fewd-breakpoints" attribute
	if(typeof fewd_resizeObserver === 'undefined')
	{
		fewd_resizeObserver = new ResizeObserver(function(entries)
		{
			for(let entry of entries)
			{
				// If current entry does not have a "data-fewd-breakpoints" attribute :
				// Does nothing on it
				var data = entry.target.getAttribute('data-fewd-breakpoints');
				if(data === null)
				{
					continue;
				}

				// Gets breakpoints
				var breakpoints = JSON.parse(data);

				// Get current width
				var width = entry.contentRect.width;
				var style = '';

				// Applies the matching breakpoints as a CSS class
				Object.keys(breakpoints).forEach(function(breakpoint)
				{
					var minWidth = breakpoints[breakpoint];

					if((style === '') && (width < minWidth))
					{
						style = breakpoint;
					}

					entry.target.classList.remove(breakpoint);
				});

				// Applies the right style
				if(style !== '')
				{
					entry.target.classList.add(style);
				}
			}
		});
	}

	// For each element corresponding to selector :
	var elements = document.querySelectorAll(selector);
	elements.forEach(element =>
	{
		// Applies breakpoints
		element.setAttribute('data-fewd-breakpoints', breakpoints);

		// Observes element
		fewd_resizeObserver.observe(element);
	});
}
