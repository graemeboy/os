// We're going to load all design data into an assoc. array
var designs = {};
jQuery(document).ready(function($) 
{
	var numDesigns; // holds the total number of designs
	var numCustomDesigns = customDesigns.length;
	console.log("Custom num: " + numCustomDesigns);
	
	$('#hidden-design').val(curDesign);
	$('#hidden-skin-id').val(skinID);
	
	getDesignInfo();
	getFonts();
	var apiURL;
	var addedScripts = [];
	var availableFonts = [];
	savedDesign = false;
	
	$('#next-design').click(function(e) {
		e.preventDefault();
		if ($(this).hasClass('ois-design-change-possible'))
		{
			showDesign(++curDesign,  numDesigns);
			$('#hidden-design').val(curDesign);
		}
	}); // on #next-design click
	$('#previous-design').click(function(e) {	
		e.preventDefault();
		if (curDesign != 1) {
			showDesign(--curDesign, numDesigns);
			$('#hidden-design').val(curDesign);
		} // if curDesign != 1
	});

	function showDesign(curDesign, numDesigns) {
		// Clear the design area
		jQuery('#ois-design-area').html($('#ois_add_loader').html());
		$('#ois-current-design').text(curDesign);
		// Clear the control area
		jQuery('#ois-editing-area').html('');
		
		// Update the hidden field
		$('#hidden-design').val(curDesign);
		
		// Let's perform some styling.
		// PREV Design
		if (curDesign <= 1)
		{
			if ($('#previous-design').hasClass('ois-design-change-possible'))
			{
				$('#previous-design').removeClass('ois-design-change-possible');
			} // if
		} // if
		else // designNum > 1
		{
			// We are beyond the first design.
			if (!$('#previous-design').hasClass('ois-design-change-possible'))
			{
				$('#previous-design').addClass('ois-design-change-possible');
			}  // if
		} // else
		
		// NEXT Design
		if (curDesign < numDesigns)
		{
			if (!$('#next-design').hasClass('ois-design-change-possible'))
			{
				$('#next-design').addClass('ois-design-change-possible');
			} // if
		} // if
		else
		{
			if ($('#next-design').hasClass('ois-design-change-possible'))
			{
				$('#next-design').removeClass('ois-design-change-possible');
			} // if
		} // else
		
		if (curDesign in designs) 
		{
			data = designs[curDesign];
			initDesign(data);
		} // if designNum
		else 
		{
			// only request the data from the external source
			// if we don't already have it.
			if (curDesign <= (numDesigns - numCustomDesigns))
			{
				getDataFromExternal(curDesign);
			} // if
			else
			{
				getCustomDesign(curDesign);
			} // else
			
		} // else
		
	} // showDesign(int)
	
	function getFonts()
	{
		apiURL = extUrl + "design_fonts.json?callback=?";
		$.getJSON( apiURL , function( data ) {
			var googleFonts = data.google;
			availableFonts = data.regular;
			var fontLink;
			var fontID;
			$.each(googleFonts, function (index, name)
			{
				fontID = name.replace(' ', '+');
				fontLink = document.createElement('link');
				fontLink.href = "http://fonts.googleapis.com/css?family=" + fontID + ""; 
				fontLink.rel = "stylesheet";
				fontLink.type = "text/css";
				document.getElementsByTagName("head")[0].appendChild(fontLink);
				availableFonts.push('googlefont-' + name);
			});
		});
		
	}
	
	function getDesignInfo()
	{
		apiURL = extUrl + "designs_info.json?callback=?";
		$.getJSON( apiURL , function( data ) {
			numDesigns = data.numDesigns + numCustomDesigns;
			// Let's update the page with this informaiton.
			if (numDesigns > 1) // should be an integer already.
			{
				$('#ois-num-designs').text(numDesigns);
			}
			showDesign(curDesign, numDesigns);
		});
	}
	
	function getCustomDesign(curDesign)
	{
		//designs[numDesigns + i] = { "html": customDesigns[curDesign].html };
		console.log(customDesigns[numDesigns - curDesign]);
		initDesign(customDesigns[numDesigns - curDesign]);
	}

	function getDataFromExternal(designNum) {
		apiURL = extUrl + "/design" + designNum + "/request.php?callback=?";
		jQuery.post(apiURL, {
			url: "hello"
		}, function(data) {
			console.log(data);
			designs[designNum] = data;
			initDesign(data);
		}, "json"); // function (data), .post
	} // getDataFromExternal (int)

	function initDesign(data) {
		// Load the css filename
		loadExternalCSS(data.css);
		// Load the html into the design area
		var template = data.html;
		$('#hidden-template').val(template);
		if (data.template_form) {
			$('#hidden-template-form').val(data.template_form);
			$('#hidden-template-css').val(data.css);
			template = template.replace('{{optin_form}}', data.template_form);
		}
		jQuery('#ois-design-area').html(template);
		var attributes = data.attributes;
		var def;
		jQuery.each(attributes, function(index, attr) {
			// Get the default value.
			// We will only do this in the case of new skins.
			// In both cases, we need a unique id for the attribute.
			// If we're not using a new skin, we'll look up the saved setting.
			var id;
			if (attr.type == 'style') {
				// Use '_' to separate the style attribute from the element
				id = "style-" + attr.element + "_" + attr["style-attr"];
			} // if
			else {
				id = attr.type + "-" + attr.element + "_" + attr.type;
			} //else

			if (id in savedSettings) 
			{
				console.log("Saved! " + id);
				def = savedSettings[id];
			} // if
			else 
			{
				def = attr["default"];
			} // else

			var desc = attr.description;
			var targetElement = attr.element;
			var cl, el;
			if (attr.type == 'text' || attr.type == 'button-text' || attr.type == 'placeholder-text') { // we just make a simple textbox input
				if (attr.type == 'text') {
					cl = 'form-control ois-text-input ois_textbox';
					$('.' + targetElement).text(def); // set current to default
				} // if type is text
				else if (attr.type == 'button-text') {
					cl = 'form-control ois-button-text-input ois_textbox';
					$('.' + targetElement).val(def); // set current to default
				} // else if type is button-text
				else if (attr.type == 'placeholder-text') {
					cl = 'form-control ois-placeholder-text-input ois_textbox';
					// set current to default
					$('.' + targetElement).attr('placeholder', def);
				} // else if type is placeholder-text
				// Create and append a new "text element" input
				appendNewTextElement(cl, targetElement, def, desc, id);
			} // if attr is text
			else if (attr.type == 'style') {
				var styleAttr = attr['style-attr'];
				$('.' + targetElement).css(styleAttr, def);
				cl = 'form-control ois-style-input ois_textbox';
				appendNewStyleElement(cl, targetElement, def, styleAttr, desc, id);
			} // else if attr is style
			else if (attr.type == 'textarea') { // We just need a bigger size text input here.
				// This often includes code, e.g. Social Networking button code.
				cl = 'form-control ois-textarea-input ois_textbox';
				// let's make sure that we take out scripts and put them in the head instead.
				
				$('.' + targetElement).html(def); // set current to default
				appendNewTextarea(cl, targetElement, def, desc, id);
			} // else if textarea
			else if (attr.type == 'font')
			{
				cl = 'form-control ois-font-select';
				$('.' + targetElement).css({'font-family': def});
				appendNewFontSelection(cl, targetElement, def, desc, id);
			}
			else if (attr.type == 'align')
			{
				cl = 'form-control ois-align-select';
				$('.' + targetElement).css({'text-align': def});
				appendNewAlignSelection(cl, targetElement, def, desc, id);
			}
		}); // .each
		addActionListeners();
	} // initDesign(data)
	
	function appendNewAlignSelection(classNames, targetElement, defaultValue, desc, id)
	{
		el = document.createElement("select");
		var optionEl;
		var alignments = ['Left', 'Center', 'Right'];
		
		for (var i = 0; i < 3; i++)
		{
			optionEl = document.createElement("option");
			optionEl.value = alignments[i].toLowerCase();
			optionEl.text = alignments[i];
			el.appendChild(optionEl);
		}
		
		addAttrToEl(el, classNames, id, targetElement, defaultValue);
		oisAppendElement(el, desc);
			
	} // appendNewAlignSelection ()
	function appendNewFontSelection(classNames, targetElement, defaultValue, desc, id)
	{
		el = document.createElement("select");
		var optionEl;
		
		for (var i = 0; i < availableFonts.length; i++)
		{
			optionEl = document.createElement("option");
			optionEl.value = availableFonts[i];
			optionEl.text = availableFonts[i].replace('googlefont-', '');
			el.appendChild(optionEl);
		}
		
		addAttrToEl(el, classNames, id, targetElement, defaultValue);
		oisAppendElement(el, desc);
	}
		
	/* 
		Add some comment attributes to the element
		Including classes, name.
	*/
	function addAttrToEl(el, classNames, id, targetElement, defaultValue)
	{
		$(el).attr('class', classNames);
		$(el).attr('data-ois-model', targetElement);
		$(el).val(defaultValue); // set input value to default
		$(el).attr('name', 'design-setting_' + id); // prepend DesignSetting_ to know that this is a design.
		$(el).attr('id', id);
		//return el;
	}
	/*
		Function: appendNewTextElement
		Desc: creates a new input element, with a default value,
			class names, and a data-ois-model attribute = targetElement.
		Pre: none.
		Post: appends a new input element to #editingArea
	*/
	function appendNewTextElement(classNames, targetElement, defaultValue, desc, id) {
		el = document.createElement('input');
		$(el).attr('type', 'text');
		addAttrToEl(el, classNames, id, targetElement, defaultValue);
		oisAppendElement(el, desc);
	}

	function appendNewTextarea(classNames, targetElement, defaultValue, desc, id) {
		el = document.createElement('textarea');
		addAttrToEl(el, classNames, id, targetElement, defaultValue);
		oisAppendElement(el, desc);
	}

	function appendNewStyleElement(classNames, targetElement, defaultValue, styleAttr, desc, id) {
		var isSlider = false;
		el = document.createElement('input');
		jQuery(el).attr('type', 'text');
		addAttrToEl(el, classNames, id, targetElement, defaultValue);
		jQuery(el).attr('data-ois-style-attr', styleAttr);
		// There is something else cool we can do here.
		// If the default value ends in px, we know it's an integer that will change.
		// Therefore, we can implement a slider for such an input
		if (defaultValue.slice(-2) == 'px') {
			isSlider = true;
			var defInt = defaultValue.slice(0, -2);
			// Make a slider element
			slW = document.createElement('div'); // wrapper for slider
			$(slW).addClass('ois-admin-slider');
			sl = document.createElement('div'); // slider element
			$(sl).attr('id', id + '-slider');
			$(sl).css({'display':'inline-block', 'width': '150px', 'float': 'left;', 'margin-left': '10px'});
			$(el).css({'width': '80px'});
			$(slW).append(el); // append the element to the wrapper
			$(slW).append(sl); // append the slider to the wrapper
			oisAppendElement(slW, desc);
			// Find an appropriate maximum value.
			var maxInt;
			if (styleAttr == 'width') {
				maxInt = 1200;
			} // if width
			else if (styleAttr == 'border-radius') {
				maxInt = 50;
			} // else if border-radius
			else if (styleAttr == 'margin-top' || styleAttr == 'margin-right' || styleAttr == 'margin-bottom' || styleAttr == 'margin-left' || styleAttr == 'padding-top' || styleAttr == 'padding-right' || styleAttr == 'padding-bottom' || styleAttr == 'padding-left') {
				maxInt = 50;
			} // else if margin / padding
			else if (styleAttr == 'border-width')
			{
				maxInt = 10;
			}
			else if (styleAttr == 'font-size')
			{
				maxInt = 50;
			}
			else {
				maxInt = 3 * defInt;
			} // else
			$(sl).slider({
				value: defInt,
				min: 0,
				max: maxInt,
				slide: function(event, ui) {
					$('#' + id).val(ui.value + 'px');
					$('.' + targetElement).css(styleAttr, ui.value + 'px');
				}
			}); // turn it into a slider.
		} // if -2 = px
		else {
			oisAppendElement(el, desc);
		} // else
		if (styleAttr == 'background-color' || styleAttr == 'color' || styleAttr == 'border-color') {
			// This is a color, so we ought to have a color picker
			$(el).addClass('color-picker');
			$(el).css({'width': '100px'});
			$(el).iris({
				change: function(event, ui) {
					$('.' + targetElement).css(styleAttr, ui.color.toString());
				} // change
			} // options
			); // .iris
		} // if
	}

	function oisAppendElement(el, desc) {
		var elContainer = document.createElement('tr');
		var elDesc = document.createElement('td');
		var elTd = document.createElement('td');
		$(elDesc).addClass('ois_label_inner');
		$(elDesc).text(desc);
		$(elContainer).append(elDesc);
		$(elTd).append(el);
		$(elContainer).append(elTd);
		$('#ois-editing-area').append(elContainer);
	}
/*
		Function: loadExternalCSS
		Desc: loads an external css file, given a valid CSS Url
		Pre: CSSUrl must be a valid url
		Post: a <link> element with href = CSSUrl is appended to document head
	*/
	function loadExternalCSS(CSSUrl) {
		var fileref = document.createElement("link");
		fileref.setAttribute("rel", "stylesheet");
		fileref.setAttribute("type", "text/css");
		fileref.setAttribute("href", CSSUrl);
		// Append the css file to the head of this document
		document.getElementsByTagName("head")[0].appendChild(fileref);
	}

	function addActionListeners() {
		// Create the action listeners for text inputs
		var target;
		var styleAttr;
		var addFromScript;
		$('.ois-text-input').keyup(function() {
			target = $(this).attr('data-ois-model');
			$('.' + target).text($(this).val());
		}); // text .keyup
		$('.ois-button-text-input').keyup(function() {
			target = $(this).attr('data-ois-model');
			$('.' + target).val($(this).val());
		});
		$('.ois-placeholder-text-input').keyup(function() {
			target = $(this).attr('data-ois-model');
			$('.' + target).attr('placeholder', $(this).val());
		});
		$('.ois-style-input').keyup(function() {
			target = $(this).attr('data-ois-model');
			styleAttr = $(this).attr('data-ois-style-attr');
			$('.' + target).css(styleAttr, $(this).val());
		}); // style .keyup
		$('.ois-textarea-input').keyup(function() {
			target = $(this).attr('data-ois-model');
			addFromScript = addScriptsFromDef($(this).val());
			$('.' + target).html(addFromScript);
		}); // style .keyup
		$('.ois-font-select').change(function()
		{
			target = $(this).attr('data-ois-model');
			$('.' + target)
				.css('font-family', $("option:selected", this)
				.val()
				.replace('googlefont-', ''));
		}); // font change
		$('.ois-align-select').change(function ()
		{
			target = $(this).attr('data-ois-model');
			$('.' + target).css('text-align', $("option:selected", this).val());
		}); // alignment change
		// Make the preview sticky, now that page is set up				
		var controlAreaScrollTop = $('#ois-control-area').offset().top;
		var nonControlScrollTop = $('#ois-non-control').offset().top - 300;
		$(window).scroll(function() {
			stickyPrev();
		});
		
		var stickyPrev = function() {
			var curScrollTop = $(window).scrollTop();
			if (curScrollTop > controlAreaScrollTop && curScrollTop < nonControlScrollTop) {
				if (!$('#ois-design-area').hasClass('ois-sticky'))
				{
					$('.ois-sticky').css('margin-left', 
						$('#ois-design-area').position().left + 'px');
					$('#ois-design-area').addClass('ois-sticky');
				} // if
				// Move it downwards as the user scrolls.
				//$('#ois-design-area').css('top', 95 + curScrollTop - controlAreaScrollTop);
			} // if
			else {
				if ($('#ois-design-area').hasClass('ois-sticky'))
				{
					$('#ois-design-area').removeClass('ois-sticky');
				} // if
			} // else
		}; // stickyPrev()
		$('#ois-editing-area').click(function(e) {
			if (!$(e.target).is('input[type="submit"]')) {
				if (!$(e.target).is(".color-picker, .iris-picker, .iris-picker-inner")) {
					$('.color-picker').iris('hide');
					return false;
				} // if
				$('.color-picker').click(function() {
					$('.color-picker').iris('hide');
					$(this).iris('show');
					return false;
				}); // .color-picker click
			} // if
		}); // document.click
	} // addActionListeners
	
	$('#ois_select_page').change(function ()
	{
		$('#ois_redirect_url').val($("option:selected", this).val());
	}); // ois_select_page.change
}); // document.ready($)