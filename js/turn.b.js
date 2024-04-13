var Quote = Backbone.Model.extend({
	urlRoot: "https://api.forismatic.com/api/1.0/?method=getQuote&lang=en&format=jsonp&jsonp=?",
	parse: function(response) {
		if (!response.quoteAuthor)
			response.quoteAuthor = "Unknown";
		response.twitterShareLink = "https://twitter.com/share?text=" + encodeURIComponent(response.quoteText + " - " + response.quoteAuthor) + "&url=" + response.quoteLink;
		response.googleShareLink = "https://plus.google.com/share?url=" + response.quoteLink;
		return response;
	}
});

var QuoteView = Backbone.View.extend({
	template: _.template($("#quote-template").html()),
	initialize: function() {
		this.model.on("change", this.render, this);
		this.model.fetch();
	},
	render: function() {
		if (this.model)
			this.$el.html(this.template(this.model.attributes));
		return this;
	}
});

var BookView = Backbone.View.extend({
	el: $("#book-content"),
	template: _.template($("#book-template").html()),
	events: {
		"click #prev-button": "prevPage",
		"click #next-button": "nextPage",
		"click #play-button": "playPages"
	},
	ui: {
		pages: "#pages",
		hardcover: "#hardcover",
		placeholder: "#placeholder",
		flipcontent: "#flipcontent",
		firstPage: "#first-page",
		secondPage: "#second-page",
		prevButton: "#prev-button",
		playButton: "#play-button",
		nextButton: "#next-button"
	},
	loadHtmlString: `<div class="quote-content">
		<i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i>
		<span class="sr-only">Loading...</span>
	</div>`,
	hPadding: 40,
	vPadding: 30,
	flipInterval: 5000,
	flipIntervalId: null,
	initialize: function(options) {
		this.options = options || {};
		if(_.isString(this.options.loadHtmlString))
			this.loadHtmlString = this.options.loadHtmlString;
		if(_.isNumber(this.options.hPadding))
			this.hPadding = this.options.hPadding;
		if(_.isNumber(this.options.vPadding))
			this.vPadding = this.options.vPadding;
		if(_.isNumber(this.options.flipInterval))
			this.flipInterval = this.options.flipInterval;
		
		_.bindAll(this, "resize", "render", "renderBook", "nextPage");
		$(window).on("resize", this.resize);
		this.render();
	},
	render: function() {
		this.$el.html(this.template());
		
		for(var el in this.ui) {
			this.ui[el] = $(this.ui[el]);
		}
		
		this.ui.hardcover.on("load", this.renderBook);
		
		var pagesTopSpacing = Math.floor(this.vPadding / 2),
				pagesSideSpacing = Math.floor(this.hPadding / 2);
		this.ui.placeholder.css("top", pagesTopSpacing).css("right", pagesSideSpacing);
		this.ui.flipcontent.css("top", pagesTopSpacing).css("left", pagesSideSpacing);
	},
	renderBook: function() {		
		this.ui.pages.hide();
		this.ui.placeholder.html(this.loadHtmlString);
		this.ui.placeholder.width(Math.floor((this.ui.hardcover.width() - this.hPadding) / 2)).height(this.ui.hardcover.height() - this.vPadding);
		
		this.ui.firstPage.html(this.loadHtmlString); 
		this.ui.secondPage.html(this.loadHtmlString);
	
		var firstQuote = new Quote(),
				firstQuoteView = new QuoteView({
					model: firstQuote,
					el: this.ui.firstPage
				}),
				secondQuote = new Quote(),
				secondQuoteView = new QuoteView({
					model: secondQuote,
					el: this.ui.secondPage
				});
		
		this.ui.pages.turn({ pages: 200 });
		this.ui.pages.bind("turning", { view: this }, function(e, page) {
			var view = e.data.view,
					range = $(this).turn("range", page);
			if(page === 1)
				view.ui.prevButton.addClass("unactive");
			else if(page === 2)
				view.ui.prevButton.removeClass("unactive");
			for (page = range[0]; page <= range[1]; page++)
				view.addPage(page, $(this));
		});
		
		this.resize();
		this.ui.pages.show();
		this.ui.placeholder.hide();
		
		this.ui.prevButton.addClass("unactive");
	},
	addPage: function(page, book) {
		if (!book.turn("hasPage", page)) {
			if (page % 2 === 0) {
				var element = $("<div />");
				book.turn("addPage", element, page);
			} else {
				var element = $("<div />").html(this.loadHtmlString);
				book.turn("addPage", element, page);
				var quote = new Quote(),
						quoteView = new QuoteView({
							model: quote,
							el: element
						});
			}
		}
	},
	nextPage: function() {
		this.ui.pages.turn("next");
	},
	prevPage: function() {
		this.ui.pages.turn("previous");
	},
	playPages: function() {
		if(this.ui.playButton.hasClass("fa-play-circle-o")) {
			this.nextPage();
			this.flipIntervalId = setInterval(this.nextPage, this.flipInterval);
		}
		else
			clearInterval(this.flipIntervalId);
		this.ui.playButton.toggleClass("fa-play-circle-o fa-pause-circle-o");
	},
	resize: function() {
		this.ui.pages.turn("size", this.ui.hardcover.width() - this.hPadding, this.ui.hardcover.height() - this.vPadding);
	}
});

$(document).ready(function() {
	var bookView = new BookView({ flipInterval: 10000 });
});