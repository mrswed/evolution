<a [+phx:if=`[+id+]`:is=`[*id*]`:or:is=`[*parent*]`:then=`[+link_attributes:pregreplace=`/class=['"]([^'"]+)['"]/||class="\1 active"`+]`:else=`[+link_attributes+]`+]  href="[(site_url)][~[+id+]~]" title="[+longtitle:ifempty=`[+pagetitle+]`+]">[+pagetitle+]</a>
