<?php

namespace FAU\CRIS;

defined('ABSPATH') || exit;

//require_once(plugin_dir_path(__DIR__). 'includes/Tools.php');
//require_once('Tools.php');
use FAU\CRIS\Tools;
use function FAU\CRIS\Tools\getPageLanguage;

/**
 * Shortcode
 */
class Shortcode {
    /**
     * Der vollständige Pfad- und Dateiname der Plugin-Datei.
     * @var string
     */
    protected $pluginFile;

	/**
	 * Settings-Objekt
	 * @var object
	 */
	protected $settings;

	/**
     * Variablen Werte zuweisen.
     * @param string $pluginFile Pfad- und Dateiname der Plugin-Datei
     */
    public function __construct($pluginFile, $settings)
    {
        $this->pluginFile = $pluginFile;
	    $this->settings = $settings;
	    $this->options = $this->settings->getOptions();
    }

    /**
     * Wird ausgeführt, sobald WP, alle Plugins und das Theme
     * vollständig geladen und instanziiert sind.
     * @return void
     */
    public function onLoaded()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        add_shortcode('cris', [$this, 'crisShortcode']);
	    //add_shortcode('cris-custom', [$this, 'crisCustomShortcode']);
	    add_shortcode('cris-custom', [$this, 'crisShortcode']);
    }

    /**
     * Enqueue der Skripte.
     */
    public function enqueueScripts()
    {
        //wp_register_style('basis-shortcode', plugins_url('assets/css/shortcodes/basis-shortcode.min.css', plugin_basename($this->pluginFile)));
        //wp_register_script('basis-shortcode', plugins_url('assets/js/shortcodes/basis-shortcode.min.js', plugin_basename($this->pluginFile)));
	    wp_enqueue_style('fau-cris');
    }

    /**
     * Shortcode cris (standard)
     * @param  array   $atts    Shortcode-Attribute
     * @param  string  $content Umgebender Seiten-/Beitragsinhalt
     * @param  string  $tag     Shortcode-Tag (cris oder cris-custom)
     * @return string Gib den Inhalt zurück
     */
    public function crisShortcode($atts, $content = '', $tag)
    {
	    wp_enqueue_style('fau-cris-shortcode');
	    wp_enqueue_script('fau-cris-shortcode');
	    $parameter = self::crisShortcodeParameter($atts, $content, $tag);
		//Publications
        if (isset($parameter['show']) && $parameter['show'] == 'publications') {
            $publications = new Entities\Publications($parameter, $content, $tag, $this->options);
            return $publications->shortcodeOutput();
	    }
	    //Projects
	    if (isset($parameter['show']) && $parameter['show'] == 'projects') {
		    $output = new Entities\Projects($parameter, $content, $tag, $this->options);
		    if ($parameter['project'] != '') {
			    if (!is_array($parameter['project'])) {
				    return $output->singleProject();
			    } else {
				    return $output->flatList();
			    }
		    }
		    if (empty($parameter['order']) && ($parameter['limit'] != '' || $parameter['sortby'] != '' || $parameter['notable'] != '')) {
			    return $output->flatList();
		    }
		    return $output->orderedList();
	    }
    }

	/**
	 * Shortcode cris-custom (personalisierte Ausgabe)
	 * @param  array   $atts    Shortcode-Attribute
	 * @param  string  $content Umgebender Seiten-/Beitragsinhalt
	 * @param  string  $tag     Shortcode-Tag (cris oder cris-custom)
	 * @return string Gib den Inhalt zurück
	 */
	/*public static function crisCustomShortcode($atts, $content = null, $tag) {
		wp_enqueue_style('fau-cris-shortcode');
		wp_enqueue_script('fau-cris-shortcode');
		$parameter = self::crisShortcodeParameter($atts, $content = null, $tag);
		if ($parameter['show'] == 'publications') {
			// Publikationen
			require_once('class_Publikationen.php');
			$liste = new Publikationen($parameter['entity'], $parameter['entity_id'], '', $page_lang);
			if ($parameter['publication'] != '' && $parameter['order1'] == '') {
				return $liste->singlePub($parameter['quotation'], $content, $parameter['sc_type'], $page_lang, $parameter['display_language']);
			}
			if ($parameter['order1'] == '' && ($parameter['limit'] != '' || $parameter['sortby'] != '' || $parameter['notable'] != '')) {
				return $liste->pubListe($parameter, $content);
			}
			if (strpos($parameter['order1'], 'type') !== false) {
				return $liste->pubNachTyp($parameter, $field = '', $content);
			}
			return $liste->pubNachJahr($parameter, $field = '', $content);
		}

	}*/

	private function crisShortcodeParameter($atts, $content = '', $tag) {
		global $post;

		$shortcode_atts = shortcode_atts([
            'show' => 'publications',
            'orderby' => 'year',
            'year' => '',
            'start' => '',
            'end' => '',
            'orgid' => $this->settings->getOption('cris_general','cris_org_nr', ''),
            'organisation' => $this->settings->getOption('cris_general','cris_org_nr', ''),
            'persid' => '',
            'publication' => '',
            'pubtype' => '',
            'quotation' => '',
            'language' => '',
            'items' => '',
            'limit' => '',
            'sortby' => 'virtualdate',
            'format' => 'list',
            'display' => '',
            'award' => '',
            'awardnameid' => '',
            'type' => '',
            'subtype' => '',
            'showname' => 1,
            'showyear' => 1,
            'showawardname' => 1,
            'showimage' => '',
            'project' => '',
            'hide' => '',
            'role' => 'all',
            'status' => '',
            'patent' => '',
            'activity' => '',
            'field' => '',
            'fsp' => '',
            'fau' => '',
            'equipment' => '',
            'manufacturer' => '',
            'constructionyear' => '',
            'constructionyearstart' => '',
            'constructionyearend' => '',
            'location' => '',
            'peerreviewed' => '',
            'current' => '',
            'publications_limit' => $this->settings->getOption('cris_layout','cris_fields_num_pub', '5'),
            'name_order_plugin' => (isset($options['cris_name_order_plugin']) && !empty($options['cris_name_order_plugin'])) ? $options['cris_name_order_plugin'] : 'firstname-lastname',
            'notable' => '',
            'publications_year' => '',
            'publications_start' => '',
            'publications_end' => '',
            'publications_type' => '',
            'publications_subtype' => '',
            'publications_fau' => '',
            'publications_peerreviewed' => '',
            'publications_orderby' => '',
            'publications_notable' => '',
            'publications_quotation' => '',
            'publications_display' => 'list',
            'image_align' => 'right',
            'accordion_title' => '#name# (#year#)',
            'accordion_color' => '',
            'display_language' => getPageLanguage($post->ID),
            'page_language' => getPageLanguage($post->ID),
            'curation' => 0,
		], $atts, $tag);
		$shortcode_atts = array_map('sanitize_text_field', $shortcode_atts);

		$shortcode_atts['sc_type'] = $tag;
		$shortcode_atts['nameorder'] = $this->settings->getOption('cris_layout','cris_name_order_plugin', 'firstname-lastname');

		if ($shortcode_atts['publication'] != '') {
			$shortcode_atts['entity'] = 'id';
			$shortcode_atts['publication'] = str_replace(' ', '', $shortcode_atts['publication']);
			$shortcode_atts['publication'] = explode(',', $shortcode_atts['publication']);
			if (count($shortcode_atts['publication']) == 1) {
                $shortcode_atts['format'] = 'no-list';
            }
			$shortcode_atts['entity_id'] = $shortcode_atts['publication'];
		} elseif ($shortcode_atts['equipment'] != '') {
			$shortcode_atts['entity'] = 'equipment';
			$shortcode_atts['entity_id'] = $shortcode_atts['equipment'];
		} elseif ($shortcode_atts['field'] != '') {
		    if ($shortcode_atts['notable'] == '1') {
                $shortcode_atts['entity'] = 'field_notable';
            } else {
		        $shortcode_atts['entity'] = 'field';
            }
			if (strpos($shortcode_atts['field'], ',') !== false) {
				$shortcode_atts['field'] = str_replace(' ', '', $shortcode_atts['field']);
				$shortcode_atts['field'] = explode(',', $shortcode_atts['field']);
			}
			$shortcode_atts['entity_id'] = $shortcode_atts['field'];
        } elseif ($shortcode_atts['fsp'] != '') {
            $shortcode_atts['entity'] = 'fsp';
            if (strpos($shortcode_atts['fsp'], ',') !== false) {
                $shortcode_atts['fsp'] = str_replace(' ', '', $shortcode_atts['fsp']);
                $shortcode_atts['fsp'] = explode(',', $shortcode_atts['fsp']);
            }
            $shortcode_atts['entity_id'] = $shortcode_atts['fsp'];
        } elseif (isset($shortcode_atts['activity']) && $shortcode_atts['activity'] != '') {
			$shortcode_atts['entity'] = 'activity';
			$shortcode_atts['entity_id'] = $shortcode_atts['activity'];
		} elseif (isset($shortcode_atts['patent']) && $shortcode_atts['patent'] != '') {
			$shortcode_atts['entity'] = 'patent';
			$shortcode_atts['entity_id'] = $shortcode_atts['patent'];
		} elseif (isset($shortcode_atts['award']) && $shortcode_atts['award'] != '') {
			$shortcode_atts['entity'] = 'award';
			$shortcode_atts['entity_id'] = $shortcode_atts['award'];
		} elseif (isset($shortcode_atts['project']) && $shortcode_atts['project'] != '') {
			$shortcode_atts['entity'] = 'project';
			if (strpos($shortcode_atts['project'], ',') !== false) {
				$shortcode_atts['project'] = str_replace(' ', '', $shortcode_atts['project']);
				$shortcode_atts['project'] = explode(',', $shortcode_atts['project']);
			}
			$shortcode_atts['entity_id'] = $shortcode_atts['project'];
		} elseif (isset($shortcode_atts['awardnameid']) && $shortcode_atts['awardnameid'] != '') {
			$shortcode_atts['entity'] = 'awardnameid';
			$shortcode_atts['entity_id'] = $shortcode_atts['awardnameid'];
		} elseif (isset($shortcode_atts['persid']) && $shortcode_atts['persid'] != '') {
			$shortcode_atts['entity'] = 'person';
			if (strpos($shortcode_atts['persid'], ',') !== false) {
				$shortcode_atts['persid'] = str_replace(' ', '', $shortcode_atts['persid']);
				$shortcode_atts['persid'] = explode(',', $shortcode_atts['persid']);
			}
			$shortcode_atts['entity_id'] = $shortcode_atts['persid'];
		} elseif (isset($shortcode_atts['orgid']) && $shortcode_atts['orgid'] != '') {
			$shortcode_atts['entity'] = 'organisation';
			if (strpos($shortcode_atts['orgid'], ',') !== false) {
				$shortcode_atts['orgid'] = str_replace(' ', '', $shortcode_atts['orgid']);
				$shortcode_atts['orgid'] = explode(',', $shortcode_atts['orgid']);
			}
			$shortcode_atts['entity_id'] = $shortcode_atts['orgid'];
		} else {
			$shortcode_atts['entity'] = '';
			$shortcode_atts['entity_id'] = '';
		}

        $shortcode_atts['order'] = [];
		if (!empty($shortcode_atts['orderby'])) {
			$orderby = explode(',', $shortcode_atts['orderby'] );
			$shortcode_atts['order'] = array_map( 'trim', $orderby );
			array_splice($shortcode_atts['order'],2);
			if (!in_array($shortcode_atts['order'][0],['year','type','author', 'none']))
				$shortcode_atts['order'][0] = 'year';
			if (!isset($shortcode_atts['order'][1])
			    || !in_array($shortcode_atts['order'][1],['year','type','subtype','author'])
				|| $shortcode_atts['order'][1] == $shortcode_atts['order'][0])
				unset($shortcode_atts['order'][1]);
		}

		$shortcode_atts['hide'] = str_replace(' ', '', $shortcode_atts['hide']);
		$shortcode_atts['hide'] = explode(',', $shortcode_atts['hide']);

		if ($shortcode_atts['format'] == 'accordion' && !shortcode_exists('collapsibles'))
			$shortcode_atts['format'] == 'default';

        if (in_array($shortcode_atts['image_align'], ['left', 'right', 'none'])) {
            $shortcode_atts['image_align'] = 'align' . sanitize_text_field($shortcode_atts['image_align']);
            $shortcode_atts['image_position'] = 'top';
        } elseif (in_array($shortcode_atts['image_align'], ['bottom', 'top'])) {
            $shortcode_atts['image_align'] = 'alignnone';
            $shortcode_atts['image_position'] = sanitize_text_field($shortcode_atts['image_align']);
        } else {
            $shortcode_atts['image_align'] = 'alignright';
            $shortcode_atts['image_position'] = 'top';
        }
        // format und display abwärtskompatibel zusammenführen
        $shortcode_atts['format'] = $shortcode_atts['format'] . $shortcode_atts['display'];

        return $shortcode_atts;
    }
}