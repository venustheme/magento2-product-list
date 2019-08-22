/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
var config = {
	map: {
		"*": {
			easytab: "Ves_Productlist/js/jquery.easytabs.min",
			countdown: "Ves_Productlist/js/countdown",
			productlist: "Ves_Productlist/js/productlist",
			vesaddtocart: "Ves_Productlist/js/catalog-add-to-cart"
		}
	},
	shim: {
    	'Ves_Productlist/js/productlist': {
            deps: ['jquery']
        }
    }
};
