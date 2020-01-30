// jshint ignore: start

export const unescapeHTML = value => {
	const htmlNode = document.createElement( 'div' );
	htmlNode.innerHTML = value;
	if( htmlNode.innerText !== undefined ) {
		return htmlNode.innerText;
	}
	return htmlNode.textContent;
};

export const filterData = ( arr, sortType, allowedKeywords, bannedKeywords, maxSize, offset ) => {
    console.log("max, offset, size = " + maxSize + ", " + offset + ", " + arr.length);
	arr = Array.from( arr ).sort( (a, b) => {
		let firstElement, secondElement;
		if ( sortType === 'date_desc' || sortType === 'date_asc' ) {
			firstElement = a.pubDate;
			secondElement = b.pubDate;
		} else if ( sortType === 'title_desc' || sortType === 'title_asc' ) {
			firstElement = a.title.toUpperCase();
			secondElement = b.title.toUpperCase();
		}
		if ( firstElement < secondElement ) {
			if ( sortType === 'date_desc' || sortType === 'title_desc' ) {
				return 1;
			} else {
				return -1;
			}
		}
		if ( firstElement > secondElement ) {
			if ( sortType === 'date_desc' || sortType === 'title_desc' ) {
				return -1;
			} else {
				return 1;
			}
		}
		// names must be equal
		return 0;
	}).filter( item => {
		if ( allowedKeywords ) {
			return allowedKeywords
				.split( ',' )
				.filter( item => item.replace( /\s/g, '' ) !== '' )
				.some( el =>  item['title'].includes( el.trim() ) );
		}
		return true;
	}).filter( item => {
		if ( bannedKeywords ) {
			return bannedKeywords
				.split( ',' )
				.filter( item => item.replace( /\s/g, '' ) !== '' )
				.every( el =>  item['title'].includes( el.trim() ) === false );
		}
		return true;
	}).slice( offset, maxSize + offset );
	return arr;
};

export const inArray = ( value, arr ) => {
	if ( arr === undefined ) return false;
	let exists = false;
	for( let i = 0; i < arr.length; i++ ) {
		let name = arr[i];
		if ( name === value ) {
			exists = true;
			break;
		}
	}
	return exists;
};

export const arrangeMeta = ( values, fields ) => {
    let meta = '';

    if(fields === ''){
        fields = 'author, date, time';
    }

    let arr = fields.replace(/\s/g,'').split( ',' );

    for(let i = 0; i < arr.length; i++){
        if(typeof values[ arr[i] ] !== 'undefined'){
            meta = meta + ' ' + values[ arr[i] ];
        }
    }
    return meta;
};