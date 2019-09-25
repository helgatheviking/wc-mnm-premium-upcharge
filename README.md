# WooCommerce Mix and Match: Premium Upcharges

### What's This?

Experimental mini-extension for that adds an additional fee to the container for products that have a particular tag.

![A row of T-Shirt products where the middle shirt is showing a +$10 upcharge](https://user-images.githubusercontent.com/507025/65634691-0a2b1a80-df9c-11e9-9317-3690c6886f39.png)

### Important

1. This is proof of concept and not officially supported in any way.
2. There is no UI and you need to define both the taxonomy (defaults to product_tag) and the array of terms associated with their respective fees.
3. Your selection of 'per-item  pricing' and 'discount' are ignored, but the container will function like a per-item priced container except all the regular contents are $0. 

### How To Use

1. Download repo from Github.
2. Edit the main file and hard-code your adjustments
	2a. Adjust the `$taxonomy` variable if using a taxonomy other than `product_tag`
	2b. Adjust the `$upcharges` array with the terms and their upcharges as key/value pairs. 
3. Upload and activate plugin.
4. Create a new Mix and Match product per usual. 
