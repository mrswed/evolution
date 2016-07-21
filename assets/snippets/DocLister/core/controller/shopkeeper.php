<?php
if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}

/**
 * site_content controller
 * @see http://modx.im/blog/addons/374.html
 *
 * @category controller
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author Agel_Nash <Agel_Nash@xaker.ru>, kabachello <kabachnik@hotmail.com>
 */
include_once(dirname(__FILE__) . "/site_content.php");
class shopkeeperDocLister extends site_contentDocLister
{
    public function __construct($modx, $cfg = array(), $startTime = null)
    {
        $cfg = array_merge(array('tvValuesTable' => 'catalog_tmplvar_contentvalues'), $cfg);
        parent::__construct($modx, $cfg, $startTime);
    }

    /**
     * @absctract
     */
    public function getUrl($id = 0)
    {
        $id = ((int)$id > 0) ? (int)$id : $this->getCurrentMODXPageID();
		
        $link = $this->checkExtender('request') ? $this->extender['request']->getLink() : $this->getRequest();
        if($id == $this->modx->config['site_start']){
            $url = $this->modx->config['site_url'].($link != '' ? "?{$link}" : "");
        }else{
            $url = $this->modx->makeUrl($id, '', $link, $this->getCFGDef('urlScheme', ''));
        }
        return $url;
    }


    /**
     * @todo set correct active placeholder if you work with other table. Because $item['id'] can differ of $modx->documentIdentifier (for other controller)
     * @todo set author placeholder (author name). Get id from Createdby OR editedby AND get info from extender user
     * @todo set filter placeholder with string filtering for insert URL
     */
    public function _render($tpl = '')
    {
        $out = '';
        if ($tpl == '') {
            $tpl = $this->getCFGDef('tpl', '@CODE:<a href="[+url+]">[+pagetitle+]</a><br />');
        }
        if ($tpl != '') {
            $this->toPlaceholders(count($this->_docs), 1, "display"); // [+display+] - сколько показано на странице.

            $i = 1;
            $sysPlh = $this->renameKeyArr($this->_plh, $this->getCFGDef("sysKey", "dl"));
            if (count($this->_docs) > 0) {
                /**
                 * @var $extUser user_DL_Extender
                 */
                if ($extUser = $this->getExtender('user')) {
                    $extUser->init($this, array('fields' => $this->getCFGDef("userFields", "")));
                }

                /**
                 * @var $extSummary summary_DL_Extender
                 */
                $extSummary = $this->getExtender('summary');

                /**
                 * @var $extPrepare prepare_DL_Extender
                 */
                $extPrepare = $this->getExtender('prepare');

				/**
                 * @var $extJotCount jotcount_DL_Extender
                 */
                $extJotCount = $this->getCFGdef('jotcount', 0) ? $this->getExtender('jotcount', true) : NULL;

                if ($extJotCount) {
                    $comments = $extJotCount->countComments(array_keys($this->_docs));
                }

				$this->skippedDocs = 0;
                foreach ($this->_docs as $item) {
					$this->renderTPL = $tpl;
                    if ($extUser) {
                        $item = $extUser->setUserData($item); //[+user.id.createdby+], [+user.fullname.publishedby+], [+dl.user.publishedby+]....
                    }

                    $item['summary'] = $extSummary ? $this->getSummary($item, $extSummary, '', 'content') : '';

					if ($extJotCount) {
                        $item['jotcount'] = APIHelpers::getkey($comments, $item['id'], 0);
                    }

                    $item = array_merge($item, $sysPlh); //inside the chunks available all placeholders set via $modx->toPlaceholders with prefix id, and with prefix sysKey
					$item['iteration'] = $i; //[+iteration+] - Number element. Starting from zero

                    if($this->getCFGDef('makeUrl', 1)){
						if($item['type'] == 'reference'){
							$item['url'] = is_numeric($item['content']) ? $this->modx->makeUrl($item['content'], '', '', $this->getCFGDef('urlScheme', '')) : $item['content'];
						}else{
							$item['url'] = $this->modx->makeUrl($item['id'], '', '', $this->getCFGDef('urlScheme', ''));
						}
					}

                    $item['date'] = $item['createdon'] + $this->modx->config['server_offset_time'];
                    if ($this->getCFGDef('dateFormat', '%d.%b.%y %H:%M') != '') {
                        $item['date'] = strftime($this->getCFGDef('dateFormat', '%d.%b.%y %H:%M'), $item['date']);
                    }

					$findTpl = $this->renderTPL;
					$tmp = $this->uniformPrepare($item, $i);
					extract($tmp, EXTR_SKIP);
					if ($this->renderTPL == '') {
						$this->renderTPL = $findTpl;
					}

                    if ($extPrepare) {
                        $item = $extPrepare->init($this, array(
                            'data' => $item,
                            'nameParam' => 'prepare'
                        ));
                        if (is_bool($item) && $item === false) {
							$this->skippedDocs++;
                            continue;
                        }
                    }
                    $tmp = $this->parseChunk($this->renderTPL, $item);

                    if ($this->getCFGDef('contentPlaceholder', 0) !== 0) {
                        $this->toPlaceholders($tmp, 1, "item[" . $i . "]"); // [+item[x]+] – individual placeholder for each iteration documents on this page
                    }
                    $out .= $tmp;
                    $i++;
                }
            } else {
                $noneTPL = $this->getCFGDef("noneTPL", "");
                $out = ($noneTPL != '') ? $this->parseChunk($noneTPL, $sysPlh) : '';
            }
            $out = $this->renderWrap($out);
        } else {
            $out = 'no template';
        }

        return $this->toPlaceholders($out);
    }

    /**
     * document
     */

    // @abstract
    public function getChildrenCount()
    {
        $out = 0;
        $sanitarInIDs = $this->sanitarIn($this->IDs);
        if ($sanitarInIDs != "''" || $this->getCFGDef('ignoreEmpty', '0')) {
            $where = $this->getCFGDef('addWhereList', '');
            $where = sqlHelper::trimLogicalOp($where);
            $where = ($where ? $where . ' AND ' : '') . $this->_filters['where'];
            if ($where != '' && $this->_filters['where'] != '') {
                $where .= " AND ";
            }
            $where = sqlHelper::trimLogicalOp($where);

            $where = "WHERE {$where}";
            $whereArr = array();
            if (!$this->getCFGDef('showNoPublish', 0)) {
                $whereArr[] = "c.published=1";
            }

            $tbl_site_content = $this->getTable('catalog', 'c');

            if ($sanitarInIDs != "''") {
                switch ($this->getCFGDef('idType', 'parents')) {
                    case 'parents':
						switch($this->getCFGDef('showParent', '0')){
							case '-1':
								$tmpWhere = "c.parent IN (" . $sanitarInIDs . ")";
								break;
							case 0:
								$tmpWhere = "c.parent IN ({$sanitarInIDs}) AND c.id NOT IN({$sanitarInIDs})";
								break;
							case 1:
							default:
								$tmpWhere = "(c.parent IN ({$sanitarInIDs}) OR c.id IN({$sanitarInIDs}))";
								break;
						}
                        if (($addDocs = $this->getCFGDef('documents', '')) != '') {
                            $addDocs = $this->sanitarIn($this->cleanIDs($addDocs));
                            $whereArr[] = "((" . $tmpWhere . ") OR c.id IN({$addDocs}))";
                        } else {
                            $whereArr[] = $tmpWhere;
                        }
                        break;
                    case 'documents':
                        $whereArr[] = "c.id IN({$sanitarInIDs})";
                        break;
                }
            }
            $from = $tbl_site_content . " " . $this->_filters['join'];
            $where = sqlHelper::trimLogicalOp($where);

            if (trim($where) != 'WHERE') {
                $where .= " AND ";
            }

            $where .= implode(" AND ", $whereArr);
            $where = sqlHelper::trimLogicalOp($where);

            if (trim($where) == 'WHERE') {
                $where = '';
            }
            $group = $this->getGroupSQL($this->getCFGDef('groupBy', 'c.id'));
            $sort = $this->SortOrderSQL("c.createdon");
            list($from) = $this->injectSortByTV($from, $sort);

            $rs = $this->dbQuery("SELECT count(*) FROM (SELECT count(*) FROM {$from} {$where} {$group}) as `tmp`");
            $out = $this->modx->db->getValue($rs);
        }
        return $out;
    }

    protected function getDocList()
    {
        $out = array();
        $sanitarInIDs = $this->sanitarIn($this->IDs);
        if ($sanitarInIDs != "''" || $this->getCFGDef('ignoreEmpty', '0')) {
            $where = $this->getCFGDef('addWhereList', '');
            $where = sqlHelper::trimLogicalOp($where);

            $where = ($where ? $where . ' AND ' : '') . $this->_filters['where'];
            $where = sqlHelper::trimLogicalOp($where);

            $tbl_site_content = $this->getTable('catalog', 'c');
            if ($sanitarInIDs != "''") {
                $where .= ($where ? " AND " : "") . "c.id IN ({$sanitarInIDs}) AND";
            }
            $where = sqlHelper::trimLogicalOp($where);

            if ($this->getCFGDef('showNoPublish', 0)) {
                if ($where != '') {
                    $where = "WHERE {$where}";
                } else {
                    $where = '';
                }
            } else {
                if ($where != '') {
                    $where = "WHERE {$where} AND ";
                } else {
                    $where = "WHERE {$where} ";
                }
                $where .= "c.published=1";
            }


            $fields = $this->getCFGDef('selectFields', 'c.*');
            $group = $this->getGroupSQL($this->getCFGDef('groupBy', 'c.id'));
            $sort = $this->SortOrderSQL("c.createdon");
            list($tbl_site_content, $sort) = $this->injectSortByTV($tbl_site_content . ' ' . $this->_filters['join'], $sort);

            $limit = $this->LimitSQL($this->getCFGDef('queryLimit', 0));

            $rs = $this->dbQuery("SELECT {$fields} FROM {$tbl_site_content} {$where} {$group} {$sort} {$limit}");

            $rows = $this->modx->db->makeArray($rs);

            foreach ($rows as $item) {
                $out[$item['id']] = $item;
            }
        }
        return $out;
    }

    public function getChildrenFolder($id)
    {
        $where = $this->getCFGDef('addWhereFolder', '');
        $where = sqlHelper::trimLogicalOp($where);
        if ($where != '') {
            $where .= " AND ";
        }

        $tbl_site_content = $this->getTable('site_content', 'c');
        $sanitarInIDs = $this->sanitarIn($id);
        if ($this->getCFGDef('showNoPublish', 0)) {
            $where = "WHERE {$where} c.parent IN ({$sanitarInIDs}";
        } else {
            $where = "WHERE {$where} c.parent IN ({$sanitarInIDs}) AND c.deleted=0 AND c.published=1";
        }

        $rs = $this->dbQuery("SELECT id FROM {$tbl_site_content} {$where} AND c.id IN(SELECT DISTINCT s.parent FROM " . $this->getTable('catalog', 's') . ")");

        $rows = $this->modx->db->makeArray($rs);
        $out = array();
        foreach ($rows as $item) {
            $out[] = $item['id'];
        }
        return $out;
    }

    protected function getChildrenList()
    {
		$where = array();
		$out = array();
		
		$tmpWhere = $this->getCFGDef('addWhereList', '');
		$tmpWhere = sqlHelper::trimLogicalOp($tmpWhere);
		if (!empty($tmpWhere)) {
			$where[] = $tmpWhere;
		}

		$tmpWhere = sqlHelper::trimLogicalOp($this->_filters['where']);
		if (!empty($tmpWhere)) {
			$where[] = $tmpWhere;
		}

        $tbl_site_content = $this->getTable('catalog', 'c');

        $sort = $this->SortOrderSQL("c.createdon");
        list($from, $sort) = $this->injectSortByTV($tbl_site_content . ' ' . $this->_filters['join'], $sort);
        $sanitarInIDs = $this->sanitarIn($this->IDs);

        $tmpWhere = null;

		if ($sanitarInIDs != "''") {
			switch($this->getCFGDef('showParent', '0')){
				case '-1':
					$tmpWhere = "c.parent IN (" . $sanitarInIDs . ")";
					break;
				case 0:
					$tmpWhere = "c.parent IN (" . $sanitarInIDs . ") AND c.id NOT IN(" . $sanitarInIDs . ")";
					break;
				case 1:
				default:
					$tmpWhere = "(c.parent IN (" . $sanitarInIDs . ") OR c.id IN({$sanitarInIDs}))";
					break;
			}
		}
		if (($addDocs = $this->getCFGDef('documents', '')) != '') {
			$addDocs = $this->sanitarIn($this->cleanIDs($addDocs));
			if(empty($tmpWhere)){
				$tmpWhere = "c.id IN({$addDocs})";
			}else{
				$tmpWhere = "((" . $tmpWhere . ") OR c.id IN({$addDocs}))";
			}
		}
		if (!empty($tmpWhere)) {
			$where[] = $tmpWhere;
		}
		if (!$this->getCFGDef('showNoPublish', 0)) {
			$where[] = "c.published=1";
		}
		if (!empty($where)) {
			$where = "WHERE " . implode(" AND ", $where);
		} else {
			$where = '';
		}
        $fields = $this->getCFGDef('selectFields', 'c.*');
		$group = $this->getGroupSQL($this->getCFGDef('groupBy', 'c.id'));
		if ($sanitarInIDs != "''" || $this->getCFGDef('ignoreEmpty', '0')) {
			$sql = $this->dbQuery("SELECT {$fields} FROM " . $from . " " . $where . " " .
				$group . " ".
				$sort . " " .
				$this->LimitSQL($this->getCFGDef('queryLimit', 0))
			);

			$rows = $this->modx->db->makeArray($sql);
			foreach ($rows as $item) {
				$out[$item['id']] = $item;
			}
		}
		return $out;
    }
}