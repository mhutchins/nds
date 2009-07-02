var NUMBER_OF_STARS = 6;

function init_rating()
{
    var ratings = document.getElementsByTagName('div');
    for (var i = 0; i < ratings.length; i++)
    {
        if (ratings[i].className != 'rating')
            continue;
            
        var rating = ratings[i].firstChild.nodeValue;
        ratings[i].removeChild(ratings[i].firstChild);
        if (rating > NUMBER_OF_STARS || rating < 0)
            continue;
		rating++;
        for (var j = 0; j < NUMBER_OF_STARS; j++)
        {
            var star = document.createElement('img');
            if (rating >= 1)
            {
                star.setAttribute('src', './local_images/stars/rating_on.gif');
                star.className = 'on';
                rating--;
            }
            else if(rating == 0.5)
            {
                star.setAttribute('src', './local_images/stars/rating_half.gif');
                star.className = 'half';
                rating = 0;
            }
            else
            {
				star.setAttribute('src', './local_images/stars/rating_off.gif');
				star.className = 'off';
            }
            var widgetId = ratings[i].getAttribute('id').substr(7);
            star.setAttribute('id', 'star_'+widgetId+'_'+j);
            star.onmouseover = new Function("evt", "displayHover("+widgetId+", "+j+");");
            star.onmouseout = new Function("evt", "displayNormal("+widgetId+", "+j+");");
            ratings[i].appendChild(star);
        } 
    }
}

function displayHover(ratingId, star)
{
    for (var i = 0; i <= star; i++)
    {
        document.getElementById('star_'+ratingId+'_'+i).setAttribute('src', './local_images/stars/rating_over.gif');
    }
}

function displayNormal(ratingId, star)
{
    for (var i = 0; i <= star; i++)
    {
        var status = document.getElementById('star_'+ratingId+'_'+i).className;
        document.getElementById('star_'+ratingId+'_'+i).setAttribute('src', './local_images/stars/rating_'+status+'.gif');
    }
}

var MAX_DUMP_DEPTH = 10;

