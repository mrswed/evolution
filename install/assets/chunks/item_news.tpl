/**
 * item_news
 * 
 * Шаблон элемента Ditto
 * 
 * @category	chunk
 * @version 	1.0
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal @modx_category Ditto
 * @internal    @installset base, sample
 */
<div class="item">
 <div class="date">[+date+]</div>
 <a href="[(site_url)][~[+id+]~]" class="caption">[+title+]</a>
 <div class="text">
  [[getImage? &tv=`image,photos` &id=`[+id+]` &save=`image-[+id+]`]]
  [+phx:if=`[+image-[+id+]+]`:ifnotempty=`<a href="[(site_url)][~[+id+]~]" class="image"><img src="[+image-[+id+]+]" alt="[+title+]" /></a>`+]
  [+introtext+]</div>
 <div class="more"><a href="[(site_url)][~[+id+]~]">Подробнее</a></div>
 <div class="enddiv"></div>

</div>
