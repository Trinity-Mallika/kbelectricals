<?php
// ******************************************************************************
// Software: mPDF, Unicode-HTML Free PDF generator                              *
// Version:  5.7     based on                                                   *
//           FPDF by Olivier PLATHEY                                            *
//           HTML2FPDF by Renato Coelho                                         *
// Date:     2013-07-14                                                         *
// Author:   Ian Back <ianb@bpm1.com>                                           *
// License:  GPL                                                                *
//                                                                              *
// Changes:  See changelog.txt                                                  *
// ******************************************************************************
define('mPDF_VERSION','5.7');

//Scale factor
define('_MPDFK', (72/25.4));

/*-- HTML-CSS --*/
define('AUTOFONT_CJK',1);
define('AUTOFONT_THAIVIET',2);
define('AUTOFONT_RTL',4);
define('AUTOFONT_INDIC',8);
define('AUTOFONT_ALL',15);

define('_BORDER_ALL',15);
define('_BORDER_TOP',8);
define('_BORDER_RIGHT',4);
define('_BORDER_BOTTOM',2);
define('_BORDER_LEFT',1);
/*-- END HTML-CSS --*/

if (!defined('_MPDF_PATH')) define('_MPDF_PATH', dirname(preg_replace('/\\\\/','/',__FILE__)) . '/');
if (!defined('_MPDF_URI')) define('_MPDF_URI',_MPDF_PATH);

require_once(_MPDF_PATH.'includes/functions.php');
require_once(_MPDF_PATH.'config_cp.php');

if (!defined('_JPGRAPH_PATH')) define("_JPGRAPH_PATH", _MPDF_PATH.'jpgraph/'); 

if (!defined('_MPDF_TEMP_PATH')) define("_MPDF_TEMP_PATH", _MPDF_PATH.'tmp/');

if (!defined('_MPDF_TTFONTPATH')) { define('_MPDF_TTFONTPATH',_MPDF_PATH.'ttfonts/'); }
if (!defined('_MPDF_TTFONTDATAPATH')) { define('_MPDF_TTFONTDATAPATH',_MPDF_PATH.'ttfontdata/'); }

$errorlevel=error_reporting();
$errorlevel=error_reporting($errorlevel & ~E_NOTICE);

//error_reporting(E_ALL);
if(function_exists("date_default_timezone_set")) {
	if (ini_get("date.timezone")=="") { date_default_timezone_set("Europe/London"); }
}
if (!function_exists("mb_strlen")) { die("Error - mPDF requires mb_string functions. Ensure that PHP is compiled with php_mbstring.dll enabled."); }

if (!defined('PHP_VERSION_ID')) {
    $version = explode('.', PHP_VERSION);
    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}
// Machine dependent number of bytes used to pack "double" into binary (used in cacheTables)
$test = pack("d", 134455.474557333333666);
define("_DSIZE", strlen($test));
class mPDF
{

///////////////////////////////
// EXTERNAL (PUBLIC) VARIABLES
// Define these in config.php
///////////////////////////////
var $CJKforceend;	// mPDF 5.6.40
// mPDF 5.6.34
var $h2bookmarks;
var $h2toc;
var $decimal_align;	// mPDF 5.6.13
var $margBuffer;	// mPDF 5.4.04
var $splitTableBorderWidth;	// mPDF 5.4.16

var $cacheTables;
var $bookmarkStyles;
var $useActiveForms;

var $repackageTTF;
var $allowCJKorphans;
var $allowCJKoverflow;

var $useKerning;
var $restrictColorSpace;
var $bleedMargin;
var $crossMarkMargin;
var $cropMarkMargin;
var $cropMarkLength;
var $nonPrintMargin;

var $PDFX;
var $PDFXauto;

var $PDFA;
var $PDFAauto;
var $ICCProfile;

var $printers_info;
var $iterationCounter;
var $smCapsScale;
var $smCapsStretch;

var $backupSubsFont;
var $backupSIPFont;
var $debugfonts;
var $useAdobeCJK;
var $percentSubset;
var $maxTTFFilesize;
var $BMPonly;

var $tableMinSizePriority;

var $dpi;
var $watermarkImgAlphaBlend;
var $watermarkImgBehind;
var $justifyB4br;
var $packTableData;
var $pgsIns;
var $simpleTables;
var $enableImports;

var $debug;
var $showStats;
var $setAutoTopMargin;
var $setAutoBottomMargin;
var $autoMarginPadding;
var $collapseBlockMargins;
var $falseBoldWeight;
var $normalLineheight;
var $progressBar;
var $incrementFPR1;
var $incrementFPR2;
var $incrementFPR3;
var $incrementFPR4;

var $SHYlang;
var $SHYleftmin;
var $SHYrightmin;
var $SHYcharmin;
var $SHYcharmax;
var $SHYlanguages;
// PageNumber Conditional Text
var $pagenumPrefix;
var $pagenumSuffix;
var $nbpgPrefix;
var $nbpgSuffix;
var $showImageErrors;
var $allow_output_buffering;
var $autoPadding;
var $useGraphs;
var $autoFontGroupSize;
var $tabSpaces;
var $useLang;
var $restoreBlockPagebreaks;
var $watermarkTextAlpha;
var $watermarkImageAlpha;
var $watermark_size;
var $watermark_pos;
var $annotSize;
var $annotMargin;
var $annotOpacity;
var $title2annots;
var $keepColumns;
var $keep_table_proportions;
var $ignore_table_widths;
var $ignore_table_percents;
var $list_align_style;
var $list_number_suffix;
var $useSubstitutions;
var $CSSselectMedia;

var $forcePortraitHeaders;
var $forcePortraitMargins;
var $displayDefaultOrientation;
var $ignore_invalid_utf8;
var $allowedCSStags;
var $onlyCoreFonts;
var $allow_charset_conversion;

var $jSWord;
var $jSmaxChar;
var $jSmaxCharLast;
var $jSmaxWordLast;

var $max_colH_correction;


var $table_error_report;
var $table_error_report_param;
var $biDirectional;
var $text_input_as_HTML; 
var $anchor2Bookmark;
var $list_indent_first_level;
var $shrink_tables_to_fit;

var $allow_html_optional_endtags;

var $img_dpi;

var $defaultheaderfontsize;
var $defaultheaderfontstyle;
var $defaultheaderline;
var $defaultfooterfontsize;
var $defaultfooterfontstyle;
var $defaultfooterline;
var $header_line_spacing;
var $footer_line_spacing;

var $pregUHCchars;
var $pregSJISchars;
var $pregCJKchars;
var $pregASCIIchars1;
var $pregASCIIchars2;
var $pregASCIIchars3;
var $pregVIETchars;
var $pregVIETPluschars;

var $pregRTLchars;
var $pregHEBchars;
var $pregARABICchars;
var $pregNonARABICchars;
// INDIC
var $pregHIchars;
var $pregBNchars;
var $pregPAchars;
var $pregGUchars;
var $pregORchars;
var $pregTAchars;
var $pregTEchars;
var $pregKNchars;
var $pregMLchars;
var $pregSHchars;
var $pregINDextra;

var $mirrorMargins;
var $default_lineheight_correction;
var $watermarkText;
var $watermarkImage;
var $showWatermarkText;
var $showWatermarkImage;

var $fontsizes;

// Aliases for backward compatability
var $UnvalidatedText;	// alias = $watermarkText
var $TopicIsUnvalidated;	// alias = $showWatermarkText
var $useOddEven;		// alias = $mirrorMargins
var $useSubstitutionsMB;	// alias = $useSubstitutions



//////////////////////
// CLASS OBJECTS
//////////////////////
var $cssmgr;
var $grad;
var $bmp;
var $wmf;
var $tocontents;
var $form;
var $directw;

//////////////////////
// INTERNAL VARIABLES
//////////////////////
var $writingToC;	// mPDF 5.6.38
// mPDF 5.6.01
var $layers;
var $current_layer;
var $open_layer_pane;
var $decimal_offset;	// mPDF 5.6.13
var $inMeter;	// mPDF 5.5.09

var $CJKleading;
var $CJKfollowing;
var $CJKoverflow;

var $textshadow;

var $colsums;
var $spanborder;
var $spanborddet;

var $visibility;

var $useRC128encryption;
var $uniqid;
var $kerning;
var $fixedlSpacing;
var $minwSpacing;
var $lSpacingCSS;
var $wSpacingCSS;

var $listDir;
var $spotColorIDs;
var $SVGcolors;
var $spotColors;
var $defTextColor;
var $defDrawColor;
var $defFillColor;

var $tableBackgrounds;
var $inlineDisplayOff;
var $kt_y00;
var $kt_p00;
var $upperCase;
var $checkSIP;
var $checkSMP;
var $checkCJK;
var $tableCJK;

var $watermarkImgAlpha;
var $PDFAXwarnings;
var $MetadataRoot; 
var $OutputIntentRoot;
var $InfoRoot; 
var $current_filename;
var $parsers;
var $current_parser;
var $_obj_stack;
var $_don_obj_stack;
var $_current_obj_id;
var $tpls;
var $tpl;
var $tplprefix;
var $_res;

var $pdf_version;
var $noImageFile;
var $lastblockbottommargin;
var $baselineC;
var $subPos;
var $subArrMB;
var $ReqFontStyle;
var $tableClipPath ;
var $forceExactLineheight;
var $listOcc; 

var $fullImageHeight;
var $inFixedPosBlock;		// Internal flag for position:fixed block
var $fixedPosBlock;		// Buffer string for position:fixed block
var $fixedPosBlockDepth;
var $fixedPosBlockBBox;
var $fixedPosBlockSave;
var $maxPosL;
var $maxPosR;

var $loaded;

var $extraFontSubsets;
var $docTemplateStart;		// Internal flag for page (page no. -1) that docTemplate starts on
var $time0;

// Classes
var $indic;
var $barcode;

var $SHYpatterns;
var $loadedSHYpatterns;
var $loadedSHYdictionary;
var $SHYdictionary;
var $SHYdictionaryWords;

var $spanbgcolorarray;
var $default_font;
var $list_lineheight;
var $headerbuffer;
var $lastblocklevelchange;
var $nestedtablejustfinished;
var $linebreakjustfinished;
var $cell_border_dominance_L;
var $cell_border_dominance_R;
var $cell_border_dominance_T;
var $cell_border_dominance_B;
var $table_keep_together;
var $plainCell_properties;
var $inherit_lineheight;
var $listitemtype;
var $shrin_k1;
var $outerfilled;

var $blockContext;
var $floatDivs;


var $patterns;
var $pageBackgrounds;

var $bodyBackgroundGradient;
var $bodyBackgroundImage;
var $bodyBackgroundColor;

var $writingHTMLheader;	// internal flag - used both for writing HTMLHeaders/Footers and FixedPos block
var $writingHTMLfooter;
var $autoFontGroups;
var $angle;

var $gradients;

var $kwt_Reference;
var $kwt_BMoutlines;
var $kwt_toc;

var $tbrot_Reference;
var $tbrot_BMoutlines;
var $tbrot_toc;

var $col_Reference;
var $col_BMoutlines;
var $col_toc;

var $currentGraphId;
var $graphs;

var $floatbuffer;
var $floatmargins;

var $bullet;
var $bulletarray;

var $rtlAsArabicFarsi;		// DEPRACATED

var $currentLang;
var $default_lang;
var $default_available_fonts;
var $pageTemplate;
var $docTemplate;
var $docTemplateContinue;

var $arabGlyphs;
var $arabHex;
var $persianGlyphs;
var $persianHex;
var $arabVowels;
var $arabPrevLink;
var $arabNextLink;


var $formobjects; // array of Form Objects for WMF
var $InlineProperties;
var $InlineAnnots;
var $ktAnnots;
var $tbrot_Annots;
var $kwt_Annots;
var $columnAnnots;
var $columnForms;

var $PageAnnots;

var $pageDim;	// Keep track of page wxh for orientation changes - set in _beginpage, used in _putannots

var $breakpoints;

var $tableLevel;
var $tbctr;
var $innermostTableLevel;
var $saveTableCounter;
var $cellBorderBuffer;

var $saveHTMLFooter_height;
var $saveHTMLFooterE_height;

var $firstPageBoxHeader;
var $firstPageBoxHeaderEven;
var $firstPageBoxFooter;
var $firstPageBoxFooterEven;

var $page_box;
var $show_marks;	// crop or cross marks

var $basepathIsLocal;

var $use_kwt;
var $kwt;
var $kwt_height;
var $kwt_y0;
var $kwt_x0;
var $kwt_buffer;
var $kwt_Links;
var $kwt_moved;
var $kwt_saved;

var $PageNumSubstitutions;

var $table_borders_separate;
var $base_table_properties;
var $borderstyles;

var $listjustfinished;
var $blockjustfinished;

var $orig_bMargin;
var $orig_tMargin;
var $orig_lMargin;
var $orig_rMargin;
var $orig_hMargin;
var $orig_fMargin;

var $pageheaders;
var $pagefooters;

var $pageHTMLheaders;
var $pageHTMLfooters;

var $saveHTMLHeader;
var $saveHTMLFooter;

var $HTMLheaderPageLinks;
var $HTMLheaderPageAnnots;
var $HTMLheaderPageForms;

// See config_fonts.php for these next 5 values
var $available_unifonts;
var $sans_fonts;
var $serif_fonts;
var $mono_fonts;
var $defaultSubsFont;

// List of ALL available CJK fonts (incl. styles) (Adobe add-ons)  hw removed
var $available_CJK_fonts;

var $HTMLHeader;
var $HTMLFooter;
var $HTMLHeaderE;
var $HTMLFooterE;
var $bufferoutput; 

var $showdefaultpagenos;	// DEPRACATED -left for backward compatability
// CJK fonts
var $Big5_widths;
var $GB_widths;
var $SJIS_widths;
var $UHC_widths;

// SetProtection
var $encrypted;	//whether document is protected
var $Uvalue;	//U entry in pdf document
var $Ovalue;	//O entry in pdf document
var $Pvalue;	//P entry in pdf document
var $enc_obj_id;	//encryption object id
var $last_rc4_key;	//last RC4 key encrypted (cached for optimisation)
var $last_rc4_key_c;	//last RC4 computed key
var $encryption_key;
var $padding;	//used for encryption


// Bookmark
var $BMoutlines;
var $OutlineRoot;
// INDEX
var $ColActive;
var $Reference;
var $CurrCol;
var $NbCol;
var $y0;			//Top ordinate of columns
var $ColL;
var $ColWidth;
var $ColGap;
// COLUMNS 
var $ColR;
var $ChangeColumn;
var $columnbuffer;
var $ColDetails;
var $columnLinks;
var $colvAlign;
// Substitutions
var $substitute;		// Array of substitution strings e.g. <ttz>112</ttz>
var $entsearch;		// Array of HTML entities (>ASCII 127) to substitute
var $entsubstitute;	// Array of substitution decimal unicode for the Hi entities


// Default values if no style sheet offered	(cf. http://www.w3.org/TR/CSS21/sample.html)
var $defaultCSS;

var $linemaxfontsize;
var $lineheight_correction;
var $lastoptionaltag;	// Save current block item which HTML specifies optionsl endtag
var $pageoutput;
var $charset_in;
var $blk;
var $blklvl;
var $ColumnAdjust;
var $ws;	// Word spacing
var $HREF;
var $pgwidth;
var $fontlist; 
var $oldx;
var $oldy;
var $B;
var $U;      //underlining flag
var $S;	// SmallCaps flag
var $I;

var $tdbegin;
var $table;
var $cell;
var $col;
var $row;

var $divbegin;
var $divalign;
var $divwidth;
var $divheight;
var $divrevert;
var $spanbgcolor;

var $spanlvl;
var $listlvl;
var $listnum;
var $listtype;
var $listoccur;
var $listlist;
var $listitem;

var $pjustfinished;
var $ignorefollowingspaces;
var $SUP;
var $SUB;
var $SMALL;
var $BIG;
var $toupper;
var $tolower;
var $capitalize;
var $dash_on;
var $dotted_on;
var $strike;

var $textbuffer;
var $currentfontstyle;
var $currentfontfamily;
var $currentfontsize;
var $colorarray;
var $bgcolorarray;
var $internallink;
var $enabledtags;

var $lineheight;
var $basepath;
var $textparam;

var $specialcontent;
var $selectoption;
var $objectbuffer;

// Table Rotation
var $table_rotate;
var $tbrot_maxw;
var $tbrot_maxh;
var $tablebuffer;
var $tbrot_align;
var $tbrot_Links;

var $divbuffer;		// Buffer used when keeping DIV on one page
var $keep_block_together;	// Keep a Block from page-break-inside: avoid
var $ktLinks;		// Keep-together Block links array
var $ktBlock;		// Keep-together Block array
var $ktForms;
var $ktReference;
var $ktBMoutlines;
var $_kttoc;

var $tbrot_y0;
var $tbrot_x0;
var $tbrot_w;
var $tbrot_h;

var $mb_enc;
var $directionality;

var $extgstates; // Used for alpha channel - Transparency (Watermark)
var $mgl;
var $mgt;
var $mgr;
var $mgb;

var $tts;
var $ttz;
var $tta;

var $headerDetails;
var $footerDetails;

// Best to alter the below variables using default stylesheet above
var $page_break_after_avoid;
var $margin_bottom_collapse;
var $list_indent;
var $list_align;
var $list_margin_bottom; 
var $default_font_size;	// in pts
var $original_default_font_size;	// used to save default sizes when using table default
var $original_default_font;
var $watermark_font;
var $defaultAlign;

// TABLE
var $defaultTableAlign;
var $tablethead;
var $thead_font_weight;
var $thead_font_style;
var $thead_font_smCaps;
var $thead_valign_default;
var $thead_textalign_default;
var $tabletfoot;
var $tfoot_font_weight;
var $tfoot_font_style;
var $tfoot_font_smCaps;
var $tfoot_valign_default;
var $tfoot_textalign_default;

var $trow_text_rotate;

var $cellPaddingL;
var $cellPaddingR;
var $cellPaddingT;
var $cellPaddingB;
var $table_lineheight;
var $table_border_attr_set;
var $table_border_css_set;

var $shrin_k;			// factor with which to shrink tables - used internally - do not change
var $shrink_this_table_to_fit;	// 0 or false to disable; value (if set) gives maximum factor to reduce fontsize
var $MarginCorrection;	// corrects for OddEven Margins
var $margin_footer;
var $margin_header;

var $tabletheadjustfinished;
var $usingCoreFont;
var $charspacing;

//Private properties FROM FPDF
var $DisplayPreferences; 
var $flowingBlockAttr;
var $page;               //current page number
var $n;                  //current object number
var $offsets;            //array of object offsets
var $buffer;             //buffer holding in-memory PDF
var $pages;              //array containing pages
var $state;              //current document state
var $compress;           //compression flag
var $DefOrientation;     //default orientation
var $CurOrientation;     //current orientation
var $OrientationChanges; //array indicating orientation changes
var $k;                  //scale factor (number of points in user unit)
var $fwPt;
var $fhPt;         //dimensions of page format in points
var $fw;
var $fh;             //dimensions of page format in user unit
var $wPt;
var $hPt;           //current dimensions of page in points
var $w;
var $h;               //current dimensions of page in user unit
var $lMargin;            //left margin
var $tMargin;            //top margin
var $rMargin;            //right margin
var $bMargin;            //page break margin
var $cMarginL;            //cell margin Left
var $cMarginR;            //cell margin Right
var $cMarginT;            //cell margin Left
var $cMarginB;            //cell margin Right
var $DeflMargin;            //Default left margin
var $DefrMargin;            //Default right margin
var $x;
var $y;               //current position in user unit for cell positioning
var $lasth;              //height of last cell printed
var $LineWidth;          //line width in user unit
var $CoreFonts;          //array of standard font names
var $fonts;              //array of used fonts
var $FontFiles;          //array of font files
var $images;             //array of used images
var $PageLinks;          //array of links in pages
var $links;              //array of internal links
var $FontFamily;         //current font family
var $FontStyle;          //current font style
var $CurrentFont;        //current font info
var $FontSizePt;         //current font size in points
var $FontSize;           //current font size in user unit
var $DrawColor;          //commands for drawing color
var $FillColor;          //commands for filling color
var $TextColor;          //commands for text color
var $ColorFlag;          //indicates whether fill and text colors are different
var $autoPageBreak;      //automatic page breaking
var $PageBreakTrigger;   //threshold used to trigger page breaks
var $InFooter;           //flag set when processing footer
var $InHTMLFooter;

var $processingFooter;   //flag set when processing footer - added for columns
var $processingHeader;   //flag set when processing header - added for columns
var $ZoomMode;           //zoom display mode
var $LayoutMode;         //layout display mode
var $title;              //title
var $subject;            //subject
var $author;             //author
var $keywords;           //keywords
var $creator;            //creator

var $aliasNbPg;       //alias for total number of pages
var $aliasNbPgGp;       //alias for total number of pages in page group
var $aliasNbPgHex;
var $aliasNbPgGpHex;

var $ispre;

var $outerblocktags;
var $innerblocktags;


// **********************************
// **********************************
// **********************************
// **********************************
// **********************************
// **********************************
// **********************************
// **********************************
// **********************************

function mPDF($mode='',$format='A4',$default_font_size=0,$default_font='',$mgl=0.5,$mgr=0.5,$mgt=0.5,$mgb=0.5,$mgh=0.5,$mgf=0.5, $orientation='P') {

/*-- BACKGROUNDS --*/
		if (!class_exists('grad', false)) { include(_MPDF_PATH.'classes/grad.php'); }
		if (empty($this->grad)) { $this->grad = new grad($this); }
/*-- END BACKGROUNDS --*/
/*-- FORMS --*/
		if (!class_exists('form', false)) { include(_MPDF_PATH.'classes/form.php'); }
		if (empty($this->form)) { $this->form = new form($this); }
/*-- END FORMS --*/

	$this->time0 = microtime(true);
	//Some checks
	$this->_dochecks();

	// Set up Aliases for backwards compatability
	$this->UnvalidatedText =& $this->watermarkText;
	$this->TopicIsUnvalidated =& $this->showWatermarkText;
	$this->AliasNbPg =& $this->aliasNbPg;
	$this->AliasNbPgGp =& $this->aliasNbPgGp;
	$this->BiDirectional =& $this->biDirectional;
	$this->Anchor2Bookmark =& $this->anchor2Bookmark;
	$this->KeepColumns =& $this->keepColumns;
	$this->useOddEven =& $this->mirrorMargins;
	$this->useSubstitutionsMB =& $this->useSubstitutions;

	$this->writingToC = false;	// mPDF 5.6.38
	// mPDF 5.6.01
	$this->layers = array();
	$this->current_layer = 0;
	$this->open_layer_pane = false;

	$this->visibility='visible';

	//Initialization of properties
	$this->spotColors=array();
	$this->spotColorIDs = array();
	$this->tableBackgrounds = array();

	$this->kt_y00 = '';
	$this->kt_p00 = '';
	$this->iterationCounter = false;
	$this->BMPonly = array();
	$this->page=0;
	$this->n=2;
	$this->buffer='';
	$this->objectbuffer = array();
	$this->pages=array();
	$this->OrientationChanges=array();
	$this->state=0;
	$this->fonts=array();
	$this->FontFiles=array();
	$this->images=array();
	$this->links=array();
	$this->InFooter=false;
	$this->processingFooter=false;
	$this->processingHeader=false;
	$this->lasth=0;
	$this->FontFamily='';
	$this->FontStyle='';
	$this->FontSizePt=9;
	$this->U=false;
	// Small Caps
	$this->upperCase = array();
	$this->S = false;
	$this->smCapsScale = 1;
	$this->smCapsStretch = 100;
	$this->margBuffer = 0;	// mPDF 5.4.04
	$this->inMeter = false;	// mPDF 5.5.09
	$this->decimal_offset = 0;

	$this->defTextColor = $this->TextColor = $this->SetTColor($this->ConvertColor(0),true);
	$this->defDrawColor = $this->DrawColor = $this->SetDColor($this->ConvertColor(0),true);
	$this->defFillColor = $this->FillColor = $this->SetFColor($this->ConvertColor(255),true);

	//SVG color names array
	//http://www.w3schools.com/css/css_colornames.asp
	$this->SVGcolors = array('antiquewhite'=>'#FAEBD7','aqua'=>'#00FFFF','aquamarine'=>'#7FFFD4','beige'=>'#F5F5DC','black'=>'#000000',
'blue'=>'#0000FF','brown'=>'#A52A2A','cadetblue'=>'#5F9EA0','chocolate'=>'#D2691E','cornflowerblue'=>'#6495ED','crimson'=>'#DC143C',
'darkblue'=>'#00008B','darkgoldenrod'=>'#B8860B','darkgreen'=>'#006400','darkmagenta'=>'#8B008B','darkorange'=>'#FF8C00',
'darkred'=>'#8B0000','darkseagreen'=>'#8FBC8F','darkslategray'=>'#2F4F4F','darkviolet'=>'#9400D3','deepskyblue'=>'#00BFFF',
'dodgerblue'=>'#1E90FF','firebrick'=>'#B22222','forestgreen'=>'#228B22','fuchsia'=>'#FF00FF','gainsboro'=>'#DCDCDC','gold'=>'#FFD700',
'gray'=>'#808080','green'=>'#008000','greenyellow'=>'#ADFF2F','hotpink'=>'#FF69B4','indigo'=>'#4B0082','khaki'=>'#F0E68C',
'lavenderblush'=>'#FFF0F5','lemonchiffon'=>'#FFFACD','lightcoral'=>'#F08080','lightgoldenrodyellow'=>'#FAFAD2','lightgreen'=>'#90EE90',
'lightsalmon'=>'#FFA07A','lightskyblue'=>'#87CEFA','lightslategray'=>'#778899','lightyellow'=>'#FFFFE0','lime'=>'#00FF00','limegreen'=>'#32CD32',
'magenta'=>'#FF00FF','maroon'=>'#800000','mediumaquamarine'=>'#66CDAA','mediumorchid'=>'#BA55D3','mediumseagreen'=>'#3CB371',
'mediumspringgreen'=>'#00FA9A','mediumvioletred'=>'#C71585','midnightblue'=>'#191970','mintcream'=>'#F5FFFA','moccasin'=>'#FFE4B5','navy'=>'#000080',
'olive'=>'#808000','orange'=>'#FFA500','orchid'=>'#DA70D6','palegreen'=>'#98FB98',
'palevioletred'=>'#D87093','peachpuff'=>'#FFDAB9','pink'=>'#FFC0CB','powderblue'=>'#B0E0E6','purple'=>'#800080',
'red'=>'#FF0000','royalblue'=>'#4169E1','salmon'=>'#FA8072','seagreen'=>'#2E8B57','sienna'=>'#A0522D','silver'=>'#C0C0C0','skyblue'=>'#87CEEB',
'slategray'=>'#708090','springgreen'=>'#00FF7F','steelblue'=>'#4682B4','tan'=>'#D2B48C','teal'=>'#008080','thistle'=>'#D8BFD8','turquoise'=>'#40E0D0',
'violetred'=>'#D02090','white'=>'#FFFFFF','yellow'=>'#FFFF00', 
'aliceblue'=>'#f0f8ff', 'azure'=>'#f0ffff', 'bisque'=>'#ffe4c4', 'blanchedalmond'=>'#ffebcd', 'blueviolet'=>'#8a2be2', 'burlywood'=>'#deb887', 
'chartreuse'=>'#7fff00', 'coral'=>'#ff7f50', 'cornsilk'=>'#fff8dc', 'cyan'=>'#00ffff', 'darkcyan'=>'#008b8b', 'darkgray'=>'#a9a9a9', 
'darkgrey'=>'#a9a9a9', 'darkkhaki'=>'#bdb76b', 'darkolivegreen'=>'#556b2f', 'darkorchid'=>'#9932cc', 'darksalmon'=>'#e9967a', 
'darkslateblue'=>'#483d8b', 'darkslategrey'=>'#2f4f4f', 'darkturquoise'=>'#00ced1', 'deeppink'=>'#ff1493', 'dimgray'=>'#696969', 
'dimgrey'=>'#696969', 'floralwhite'=>'#fffaf0', 'ghostwhite'=>'#f8f8ff', 'goldenrod'=>'#daa520', 'grey'=>'#808080', 'honeydew'=>'#f0fff0', 
'indianred'=>'#cd5c5c', 'ivory'=>'#fffff0', 'lavender'=>'#e6e6fa', 'lawngreen'=>'#7cfc00', 'lightblue'=>'#add8e6', 'lightcyan'=>'#e0ffff', 
'lightgray'=>'#d3d3d3', 'lightgrey'=>'#d3d3d3', 'lightpink'=>'#ffb6c1', 'lightseagreen'=>'#20b2aa', 'lightslategrey'=>'#778899', 
'lightsteelblue'=>'#b0c4de', 'linen'=>'#faf0e6', 'mediumblue'=>'#0000cd', 'mediumpurple'=>'#9370db', 'mediumslateblue'=>'#7b68ee', 
'mediumturquoise'=>'#48d1cc', 'mistyrose'=>'#ffe4e1', 'navajowhite'=>'#ffdead', 'oldlace'=>'#fdf5e6', 'olivedrab'=>'#6b8e23', 'orangered'=>'#ff4500', 
'palegoldenrod'=>'#eee8aa', 'paleturquoise'=>'#afeeee', 'papayawhip'=>'#ffefd5', 'peru'=>'#cd853f', 'plum'=>'#dda0dd', 'rosybrown'=>'#bc8f8f', 
'saddlebrown'=>'#8b4513', 'sandybrown'=>'#f4a460', 'seashell'=>'#fff5ee', 'slateblue'=>'#6a5acd', 'slategrey'=>'#708090', 'snow'=>'#fffafa', 
'tomato'=>'#ff6347', 'violet'=>'#ee82ee', 'wheat'=>'#f5deb3', 'whitesmoke'=>'#f5f5f5', 'yellowgreen'=>'#9acd32');

	$this->ColorFlag=false;
	$this->extgstates = array();

	$this->mb_enc='windows-1252';
	$this->directionality='ltr';
	$this->defaultAlign = 'L';
	$this->defaultTableAlign = 'L';

	$this->fixedPosBlockSave = array();
	$this->extraFontSubsets = 0;

	$this->SHYpatterns = array();
	$this->loadedSHYdictionary = false;
	$this->SHYdictionary = array();
	$this->SHYdictionaryWords = array();
	$this->blockContext = 1;
	$this->floatDivs = array();
	$this->DisplayPreferences=''; 

	$this->patterns = array();		// Tiling patterns used for backgrounds
	$this->pageBackgrounds = array();
	$this->writingHTMLheader = false;	// internal flag - used both for writing HTMLHeaders/Footers and FixedPos block
	$this->writingHTMLfooter = false;	// internal flag - used both for writing HTMLHeaders/Footers and FixedPos block
	$this->gradients = array();

	$this->kwt_Reference = array();
	$this->kwt_BMoutlines = array();
	$this->kwt_toc = array();

	$this->tbrot_Reference = array();
	$this->tbrot_BMoutlines = array();
	$this->tbrot_toc = array();

	$this->col_Reference = array();
	$this->col_BMoutlines = array();
	$this->col_toc = array();
	$this->graphs = array();

	$this->pgsIns = array();
	$this->PDFAXwarnings = array();
	$this->inlineDisplayOff = false;
	$this->kerning = false;
	$this->lSpacingCSS = '';
	$this->wSpacingCSS = '';
	$this->fixedlSpacing = false;
	$this->minwSpacing = 0;


	$this->baselineC = 0.35;	// Baseline for text
	$this->noImageFile = str_replace("\\","/",dirname(__FILE__)) . '/includes/no_image.jpg';
	$this->subPos = 0;
	$this->forceExactLineheight = false;
	$this->listOcc = 0;
	$this->normalLineheight = 1.3;
	// These are intended as configuration variables, and should be set in config.php - which will override these values; 
	// set here as failsafe as will cause an error if not defined
	$this->incrementFPR1 = 10;
	$this->incrementFPR2 = 10;
	$this->incrementFPR3 = 10;
	$this->incrementFPR4 = 10;

	$this->fullImageHeight = false;
	$this->floatbuffer = array();
	$this->floatmargins = array();
	$this->autoFontGroups = 0;
	$this->formobjects=array(); // array of Form Objects for WMF
	$this->InlineProperties=array();
	$this->InlineAnnots=array();
	$this->ktAnnots=array();
	$this->tbrot_Annots=array();
	$this->kwt_Annots=array();
	$this->columnAnnots=array();
	$this->pageDim=array();
	$this->breakpoints = array();	// used in columnbuffer
	$this->tableLevel=0;
	$this->tbctr=array();	// counter for nested tables at each level
	$this->page_box = array();
	$this->show_marks = '';	// crop or cross marks
	$this->kwt = false;
	$this->kwt_height = 0;
	$this->kwt_y0 = 0;
	$this->kwt_x0 = 0;
	$this->kwt_buffer = array();
	$this->kwt_Links = array();
	$this->kwt_moved = false;
	$this->kwt_saved = false;
	$this->PageNumSubstitutions = array();
	$this->base_table_properties=array();
	$this->borderstyles = array('inset','groove','outset','ridge','dotted','dashed','solid','double');
	$this->tbrot_align = 'C';
	$this->pageheaders=array();
	$this->pagefooters=array();

	$this->pageHTMLheaders=array();
	$this->pageHTMLfooters=array();
	$this->HTMLheaderPageLinks = array();
	$this->HTMLheaderPageAnnots = array();

	$this->ktForms = array();
	$this->HTMLheaderPageForms = array();
	$this->columnForms = array();
	$this->tbrotForms = array();
	$this->useRC128encryption = false;
	$this->uniqid = '';

	$this->bufferoutput = false; 
	$this->encrypted=false;    		//whether document is protected
	$this->BMoutlines=array();
	$this->ColActive=0;        		//Flag indicating that columns are on (the index is being processed)
	$this->Reference=array();  		//Array containing the references
	$this->CurrCol=0;              	//Current column number
	$this->ColL = array(0);			// Array of Left pos of columns - absolute - needs Margin correction for Odd-Even
	$this->ColR = array(0);			// Array of Right pos of columns - absolute pos - needs Margin correction for Odd-Even
	$this->ChangeColumn = 0;
	$this->columnbuffer = array();
	$this->ColDetails = array();		// Keeps track of some column details
	$this->columnLinks = array();		// Cross references PageLinks
	$this->substitute = array();		// Array of substitution strings e.g. <ttz>112</ttz>
	$this->entsearch = array();		// Array of HTML entities (>ASCII 127) to substitute
	$this->entsubstitute = array();	// Array of substitution decimal unicode for the Hi entities
	$this->lastoptionaltag = '';
	$this->charset_in = '';
	$this->blk = array();
	$this->blklvl = 0;
	$this->tts = false;
	$this->ttz = false;
	$this->tta = false;
	$this->ispre=false;

	$this->checkSIP = false;
	$this->checkSMP = false;
	$this->checkCJK = false;
	$this->tableCJK = false;

	$this->headerDetails=array();
	$this->footerDetails=array();
	$this->page_break_after_avoid = false;
	$this->margin_bottom_collapse = false;
	$this->tablethead = 0;
	$this->tabletfoot = 0;
	$this->table_border_attr_set = 0;
	$this->table_border_css_set = 0;
	$this->shrin_k = 1.0;
	$this->shrink_this_table_to_fit = 0;
	$this->MarginCorrection = 0;

	$this->tabletheadjustfinished = false;
	$this->usingCoreFont = false;
	$this->charspacing=0;

	$this->autoPageBreak = true;

	require(_MPDF_PATH.'config.php');	// config data

	$this->_setPageSize($format, $orientation);
	$this->DefOrientation=$orientation;

	$this->margin_header=$mgh;
	$this->margin_footer=$mgf;

	$bmargin=$mgb;

	$this->DeflMargin = $mgl;
	$this->DefrMargin = $mgr;

	$this->orig_tMargin = $mgt;
	$this->orig_bMargin = $bmargin;
	$this->orig_lMargin = $this->DeflMargin;
	$this->orig_rMargin = $this->DefrMargin;
	$this->orig_hMargin = $this->margin_header;
	$this->orig_fMargin = $this->margin_footer;

	if ($this->setAutoTopMargin=='pad') { $mgt += $this->margin_header; }
	if ($this->setAutoBottomMargin=='pad') { $mgb += $this->margin_footer; }
	$this->SetMargins($this->DeflMargin,$this->DefrMargin,$mgt);	// sets l r t margin
	//Automatic page break
	$this->SetAutoPageBreak($this->autoPageBreak,$bmargin);	// sets $this->bMargin & PageBreakTrigger

	$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;

	//Interior cell margin (1 mm) ? not used
	$this->cMarginL = 1;
	$this->cMarginR = 1;
	//Line width (0.2 mm)
	$this->LineWidth=.567/_MPDFK;

	//To make the function Footer() work - replaces {nb} with page number
	$this->AliasNbPages();
	$this->AliasNbPageGroups();

	$this->aliasNbPgHex = '{nbHEXmarker}';
	$this->aliasNbPgGpHex = '{nbpgHEXmarker}';

	//Enable all tags as default
	$this->DisableTags();
	//Full width display mode
	$this->SetDisplayMode(100);	// fullwidth?		'fullpage'
	//Compression
	$this->SetCompression(true);
	//Set default display preferences
	$this->SetDisplayPreferences(''); 

	// Font data
	require(_MPDF_PATH.'config_fonts.php');
	// Available fonts
	$this->available_unifonts = array();
	foreach ($this->fontdata AS $f => $fs) {
		if (isset($fs['R']) && $fs['R']) { $this->available_unifonts[] = $f; }
		if (isset($fs['B']) && $fs['B']) { $this->available_unifonts[] = $f.'B'; }
		if (isset($fs['I']) && $fs['I']) { $this->available_unifonts[] = $f.'I'; }
		if (isset($fs['BI']) && $fs['BI']) { $this->available_unifonts[] = $f.'BI'; }
	}

	$this->default_available_fonts = $this->available_unifonts;

	$optcore = false;
	$onlyCoreFonts = false;
	if (preg_match('/([\-+])aCJK/i',$mode, $m)) {
		preg_replace('/([\-+])aCJK/i','',$mode);
		if ($m[1]=='+') { $this->useAdobeCJK = true; }
		else { $this->useAdobeCJK = false; }
	}

	if (strlen($mode)==1) {
		if ($mode=='s') { $this->percentSubset = 100; $mode = ''; }
		else if ($mode=='c') { $onlyCoreFonts = true; $mode = ''; }
	}
	else if (substr($mode,-2)=='-s') {
		$this->percentSubset = 100; 
		$mode = substr($mode,0,strlen($mode)-2);
	}
	else if (substr($mode,-2)=='-c') {
		$onlyCoreFonts = true;
		$mode = substr($mode,0,strlen($mode)-2);
	}
	else if (substr($mode,-2)=='-x') {
		$optcore = true;
		$mode = substr($mode,0,strlen($mode)-2);
	}

	// Autodetect if mode is a language_country string (en-GB or en_GB or en)
	if ((strlen($mode) == 5 && $mode != 'UTF-8') || strlen($mode) == 2) {
		list ($coreSuitable,$mpdf_pdf_unifonts) = GetLangOpts($mode, $this->useAdobeCJK);
		if ($coreSuitable && $optcore) { $onlyCoreFonts = true; }
		if ($mpdf_pdf_unifonts) { 
			$this->RestrictUnicodeFonts($mpdf_pdf_unifonts); 
			$this->default_available_fonts = $mpdf_pdf_unifonts;
		}
		$this->currentLang = $mode;
		$this->default_lang = $mode;
	}

	$this->onlyCoreFonts =  $onlyCoreFonts;

	if ($this->onlyCoreFonts) {
		$this->setMBencoding('windows-1252');	// sets $this->mb_enc
	}
	else {
		$this->setMBencoding('UTF-8');	// sets $this->mb_enc
	}
	@mb_regex_encoding('UTF-8');	// required only for mb_ereg... and mb_split functions


	// Adobe CJK fonts
	$this->available_CJK_fonts = array('gb','big5','sjis','uhc','gbB','big5B','sjisB','uhcB','gbI','big5I','sjisI','uhcI',
		'gbBI','big5BI','sjisBI','uhcBI');


	//Standard fonts
	$this->CoreFonts=array('ccourier'=>'Courier','ccourierB'=>'Courier-Bold','ccourierI'=>'Courier-Oblique','ccourierBI'=>'Courier-BoldOblique',
		'chelvetica'=>'Helvetica','chelveticaB'=>'Helvetica-Bold','chelveticaI'=>'Helvetica-Oblique','chelveticaBI'=>'Helvetica-BoldOblique',
		'ctimes'=>'Times-Roman','ctimesB'=>'Times-Bold','ctimesI'=>'Times-Italic','ctimesBI'=>'Times-BoldItalic',
		'csymbol'=>'Symbol','czapfdingbats'=>'ZapfDingbats');
	$this->fontlist=array("ctimes","ccourier","chelvetica","csymbol","czapfdingbats");

	// Substitutions
	$this->setHiEntitySubstitutions();

	if ($this->onlyCoreFonts) {
		$this->useSubstitutions = true;
		$this->SetSubstitutions();
	}
	else { $this->useSubstitutions = false; }

/*-- HTML-CSS --*/

	if (!class_exists('cssmgr', false)) { include(_MPDF_PATH .'classes/cssmgr.php'); }
	$this->cssmgr = new cssmgr($this);
	if (file_exists(_MPDF_PATH.'mpdf.css')) {
		$css = file_get_contents(_MPDF_PATH.'mpdf.css');
		$css2 = $this->cssmgr->ReadDefaultCSS($css);
		$this->defaultCSS = $this->cssmgr->array_merge_recursive_unique($this->defaultCSS,$css2); 
	}
/*-- END HTML-CSS --*/

	if ($default_font=='') { 
	  if ($this->onlyCoreFonts) { 
		if (in_array(strtolower($this->defaultCSS['BODY']['FONT-FAMILY']),$this->mono_fonts)) { $default_font = 'ccourier'; }
		else if (in_array(strtolower($this->defaultCSS['BODY']['FONT-FAMILY']),$this->sans_fonts)) { $default_font = 'chelvetica'; }
		else { $default_font = 'ctimes'; }
	  }
	  else { $default_font = $this->defaultCSS['BODY']['FONT-FAMILY']; }
	}
	if (!$default_font_size) { 
		$mmsize = $this->ConvertSize($this->defaultCSS['BODY']['FONT-SIZE']);
		$default_font_size = $mmsize*(_MPDFK);
	}

	if ($default_font) { $this->SetDefaultFont($default_font); }
	if ($default_font_size) { $this->SetDefaultFontSize($default_font_size); }

	$this->SetLineHeight();	// lineheight is in mm

	$this->SetFColor($this->ConvertColor(255));
	$this->HREF='';
	$this->oldy=-1;
	$this->B=0;
	$this->U=false;
	$this->S=false;
	$this->I=0;

	$this->listlvl=0;
	$this->listnum=0; 
	$this->listtype='';
	$this->listoccur=array();
	$this->listlist=array();
	$this->listitem=array();

	$this->tdbegin=false; 
	$this->table=array(); 
	$this->cell=array();  
	$this->col=-1; 
	$this->row=-1; 
	$this->cellBorderBuffer = array();

	$this->divbegin=false;
	$this->divalign='';
	$this->divwidth=0; 
	$this->divheight=0; 
	$this->spanbgcolor=false;
	$this->divrevert=false;
	$this->spanborder=false;
	$this->spanborddet=array();

	$this->blockjustfinished=false;
	$this->listjustfinished=false;
	$this->ignorefollowingspaces = true; //in order to eliminate exceeding left-side spaces
	$this->toupper=false;
	$this->tolower=false;
	$this->capitalize=false;
	$this->dash_on=false;
	$this->dotted_on=false;
	$this->SUP=false;
	$this->SUB=false;
	$this->strike=false;
	$this->textshadow='';

	$this->currentfontfamily='';
	$this->currentfontsize='';
	$this->currentfontstyle='';
	$this->colorarray=array();
	$this->spanbgcolorarray=array();
	$this->textbuffer=array();
	$this->internallink=array();
	$this->basepath = "";

	$this->SetBasePath('');

	$this->textparam = array();

	$this->specialcontent = '';
	$this->selectoption = array();

/*-- IMPORTS --*/

	$this->tpls = array();
	$this->tpl = 0;
	$this->tplprefix = "/TPL";
	$this->res = array();
	if ($this->enableImports) {
		$this->SetImportUse();
	}
/*-- END IMPORTS --*/

	if ($this->progressBar) { $this->StartProgressBarOutput($this->progressBar) ;	}	// *PROGRESS-BAR*
}


function _setPageSize($format, &$orientation) {
	//Page format
	if(is_string($format))
	{
		if ($format=='') { $format = 'A4'; }
		$pfo = 'P';
		if(preg_match('/([0-9a-zA-Z]*)-L/i',$format,$m)) {	// e.g. A4-L = A4 landscape
			$format=$m[1]; 
			$pfo='L'; 
		}
		$format = $this->_getPageFormat($format);
		if (!$format) { $this->Error('Unknown page format: '.$format); }
		else { $orientation = $pfo; }

		$this->fwPt=$format[0];
		$this->fhPt=$format[1];
	}
	else
	{
		if (!$format[0] || !$format[1]) { $this->Error('Invalid page format: '.$format[0].' '.$format[1]); }
		$this->fwPt=$format[0]*_MPDFK;
		$this->fhPt=$format[1]*_MPDFK;
	}
	$this->fw=$this->fwPt/_MPDFK;
	$this->fh=$this->fhPt/_MPDFK;
	//Page orientation
	$orientation=strtolower($orientation);
	if($orientation=='p' or $orientation=='portrait')
	{
		$orientation='P';
		$this->wPt=$this->fwPt;
		$this->hPt=$this->fhPt;
	}
	elseif($orientation=='l' or $orientation=='landscape')
	{
		$orientation='L';
		$this->wPt=$this->fhPt;
		$this->hPt=$this->fwPt;
	}
	else $this->Error('Incorrect orientation: '.$orientation);
	$this->CurOrientation=$orientation;

	$this->w=$this->wPt/_MPDFK;
	$this->h=$this->hPt/_MPDFK;
}

function _getPageFormat($format) {
		switch (strtoupper($format)) {
			case '4A0': {$format = array(4767.87,6740.79); break;}
			case '2A0': {$format = array(3370.39,4767.87); break;}
			case 'A0': {$format = array(2383.94,3370.39); break;}
			case 'A1': {$format = array(1683.78,2383.94); break;}
			case 'A2': {$format = array(1190.55,1683.78); break;}
			case 'A3': {$format = array(841.89,1190.55); break;}
			case 'A4': default: {$format = array(595.28,841.89); break;}
			case 'A5': {$format = array(419.53,595.28); break;}
			case 'A6': {$format = array(297.64,419.53); break;}
			case 'A7': {$format = array(209.76,297.64); break;}
			case 'A8': {$format = array(147.40,209.76); break;}
			case 'A9': {$format = array(104.88,147.40); break;}
			case 'A10': {$format = array(73.70,104.88); break;}
			case 'B0': {$format = array(2834.65,4008.19); break;}
			case 'B1': {$format = array(2004.09,2834.65); break;}
			case 'B2': {$format = array(1417.32,2004.09); break;}
			case 'B3': {$format = array(1000.63,1417.32); break;}
			case 'B4': {$format = array(708.66,1000.63); break;}
			case 'B5': {$format = array(498.90,708.66); break;}
			case 'B6': {$format = array(354.33,498.90); break;}
			case 'B7': {$format = array(249.45,354.33); break;}
			case 'B8': {$format = array(175.75,249.45); break;}
			case 'B9': {$format = array(124.72,175.75); break;}
			case 'B10': {$format = array(87.87,124.72); break;}
			case 'C0': {$format = array(2599.37,3676.54); break;}
			case 'C1': {$format = array(1836.85,2599.37); break;}
			case 'C2': {$format = array(1298.27,1836.85); break;}
			case 'C3': {$format = array(918.43,1298.27); break;}
			case 'C4': {$format = array(649.13,918.43); break;}
			case 'C5': {$format = array(459.21,649.13); break;}
			case 'C6': {$format = array(323.15,459.21); break;}
			case 'C7': {$format = array(229.61,323.15); break;}
			case 'C8': {$format = array(161.57,229.61); break;}
			case 'C9': {$format = array(113.39,161.57); break;}
			case 'C10': {$format = array(79.37,113.39); break;}
			case 'RA0': {$format = array(2437.80,3458.27); break;}
			case 'RA1': {$format = array(1729.13,2437.80); break;}
			case 'RA2': {$format = array(1218.90,1729.13); break;}
			case 'RA3': {$format = array(864.57,1218.90); break;}
			case 'RA4': {$format = array(609.45,864.57); break;}
			case 'SRA0': {$format = array(2551.18,3628.35); break;}
			case 'SRA1': {$format = array(1814.17,2551.18); break;}
			case 'SRA2': {$format = array(1275.59,1814.17); break;}
			case 'SRA3': {$format = array(907.09,1275.59); break;}
			case 'SRA4': {$format = array(637.80,907.09); break;}
			case 'LETTER': {$format = array(612.00,792.00); break;}
			case 'LEGAL': {$format = array(612.00,1008.00); break;}
			case 'LEDGER': {$format = array(279.00,432.00); break;}
			case 'TABLOID': {$format = array(279.00,432.00); break;}
			case 'EXECUTIVE': {$format = array(521.86,756.00); break;}
			case 'FOLIO': {$format = array(612.00,936.00); break;}
			case 'B': {$format=array(362.83,561.26 );	 break;}		//	'B' format paperback size 128x198mm
			case 'A': {$format=array(314.65,504.57 );	 break;}		//	'A' format paperback size 111x178mm
			case 'DEMY': {$format=array(382.68,612.28 );  break;}		//	'Demy' format paperback size 135x216mm
			case 'ROYAL': {$format=array(433.70,663.30 );  break;}	//	'Royal' format paperback size 153x234mm
			default: $format = false;
		}
	return $format;
}


/*-- PROGRESS-BAR --*/
function StartProgressBarOutput($mode=1) {
	// must be relative path, or URI (not a file system path)
	if (!defined('_MPDF_URI')) { 
		$this->progressBar = false;
		if ($this->debug) { $this->Error("You need to define _MPDF_URI to use the progress bar!"); }
		else return false; 
	}
	$this->progressBar = $mode;
	if ($this->progbar_altHTML) {
		echo $this->progbar_altHTML;
	}
	else {
	   echo '<html>
	<head>
	<title>mPDF File Progress</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="'._MPDF_URI.'progbar.css" />
		</head>
	<body>
	<div class="main">
		<div class="heading">'.$this->progbar_heading.'</div>
		<div class="demo">
	   ';
	   if ($this->progressBar==2) { echo '		<table width="100%"><tr><td style="width: 50%;"> 
			<span class="barheading">Writing HTML code</span> <br/>

			<div class="progressBar">
			<div id="element1"  class="innerBar">&nbsp;</div>
			</div>
			<span class="code" id="box1"></span>
			</td><td style="width: 50%;">
			<span class="barheading">Autosizing elements</span> <br/>
			<div class="progressBar">
			<div id="element4"  class="innerBar">&nbsp;</div>
			</div>
			<span class="code" id="box4"></span>
			<br/><br/>
			<span class="barheading">Writing Tables</span> <br/>
			<div class="progressBar">
			<div id="element7"  class="innerBar">&nbsp;</div>
			</div>
			<span class="code" id="box7"></span>
			</td></tr>
			<tr><td><br /><br /></td><td></td></tr>
			<tr><td style="width: 50%;"> 
	'; }
	echo '			<span class="barheading">Writing PDF file</span> <br/>
			<div class="progressBar">
			<div id="element2"  class="innerBar">&nbsp;</div>
			</div>
			<span class="code" id="box2"></span>
	   ';
	   if ($this->progressBar==2) { echo '
			</td><td style="width: 50%;">
			<span class="barheading">Memory usage</span> <br/>
			<div class="progressBar">
			<div id="element5"  class="innerBar">&nbsp;</div>
			</div>
			<span id="box5">0</span> '.ini_get("memory_limit").'<br />
			<br/><br/>
			<span class="barheading">Memory usage (peak)</span> <br/>
			<div class="progressBar">
			<div id="element6"  class="innerBar">&nbsp;</div>
			</div>
			<span id="box6">0</span> '.ini_get("memory_limit").'<br />
			</td></tr>
			</table>
	   '; }
	   echo '			<br/><br/>
		<span id="box3"></span>

		</div>
	   ';
	}
	ob_flush();
      flush();
}

function UpdateProgressBar($el,$val,$txt='') {
	// $val should be a string - 5 = actual value, +15 = increment

	if ($this->progressBar<2) {
		if ($el>3) { return; }
		else if ($el ==1) { $el = 2; }
	}
	echo '<script type="text/javascript">';
	if ($val) { echo ' document.getElementById(\'element'.$el.'\').style.width=\''.$val.'%\'; '; }
	if ($txt) { echo ' document.getElementById(\'box'.$el.'\').innerHTML=\''.$txt.'\'; '; }
	if ($this->progressBar==2) { 
		$m = round(memory_get_usage(true)/1048576);
		$m2 = round(memory_get_peak_usage(true)/1048576);
		$mem = $m * 100 / (ini_get("memory_limit")+0);
		$mem2 = $m2 * 100 / (ini_get("memory_limit")+0);
		echo ' document.getElementById(\'element5\').style.width=\''.$mem.'%\'; '; 
		echo ' document.getElementById(\'element6\').style.width=\''.$mem2.'%\'; '; 
		echo ' document.getElementById(\'box5\').innerHTML=\''.$m.'MB / \'; '; 
		echo ' document.getElementById(\'box6\').innerHTML=\''.$m2.'MB / \'; '; 
	}
	echo '</script>'."\n";
	ob_flush();
	flush();
}
/*-- END PROGRESS-BAR --*/



function RestrictUnicodeFonts($res) {
	// $res = array of (Unicode) fonts to restrict to: e.g. norasi|norasiB - language specific
	if (count($res)) {	// Leave full list of available fonts if passed blank array
		$this->available_unifonts = $res;
	}
	else { $this->available_unifonts = $this->default_available_fonts; }
	if (count($this->available_unifonts) == 0) { $this->available_unifonts[] = $this->default_available_fonts[0]; }
	$this->available_unifonts = array_values($this->available_unifonts);
}


function setMBencoding($enc) {
	if ($this->mb_enc != $enc) { 
		$this->mb_enc = $enc; 
		mb_internal_encoding($this->mb_enc); 
	}
}


function SetMargins($left,$right,$top) {
	//Set left, top and right margins
	$this->lMargin=$left;
	$this->rMargin=$right;
	$this->tMargin=$top;
}

function ResetMargins() {
	//ReSet left, top margins
	if (($this->forcePortraitHeaders || $this->forcePortraitMargins) && $this->DefOrientation=='P' && $this->CurOrientation=='L') {
	    if (($this->mirrorMargins) && (($this->page)%2==0)) {	// EVEN
		$this->tMargin=$this->orig_rMargin;
		$this->bMargin=$this->orig_lMargin;
	    }
	    else {	// ODD	// OR NOT MIRRORING MARGINS/FOOTERS
		$this->tMargin=$this->orig_lMargin;
		$this->bMargin=$this->orig_rMargin;
	    }
	   $this->lMargin=$this->DeflMargin;
	   $this->rMargin=$this->DefrMargin;
	   $this->MarginCorrection = 0;
	   $this->PageBreakTrigger=$this->h-$this->bMargin;
	}
	else  if (($this->mirrorMargins) && (($this->page)%2==0)) {	// EVEN
		$this->lMargin=$this->DefrMargin;
		$this->rMargin=$this->DeflMargin;
		$this->MarginCorrection = $this->DefrMargin-$this->DeflMargin;

	}
	else {	// ODD	// OR NOT MIRRORING MARGINS/FOOTERS
		$this->lMargin=$this->DeflMargin;
		$this->rMargin=$this->DefrMargin;
		if ($this->mirrorMargins) { $this->MarginCorrection = $this->DeflMargin-$this->DefrMargin; }
	}
	$this->x=$this->lMargin;

}

function SetLeftMargin($margin) {
	//Set left margin
	$this->lMargin=$margin;
	if($this->page>0 and $this->x<$margin) $this->x=$margin;
}

function SetTopMargin($margin) {
	//Set top margin
	$this->tMargin=$margin;
}

function SetRightMargin($margin) {
	//Set right margin
	$this->rMargin=$margin;
}

function SetAutoPageBreak($auto,$margin=0) {
	//Set auto page break mode and triggering margin
	$this->autoPageBreak=$auto;
	$this->bMargin=$margin;
	$this->PageBreakTrigger=$this->h-$margin;
}

function SetDisplayMode($zoom,$layout='continuous') {
	//Set display mode in viewer
	if($zoom=='fullpage' or $zoom=='fullwidth' or $zoom=='real' or $zoom=='default' or !is_string($zoom))
		$this->ZoomMode=$zoom;
	else
		$this->Error('Incorrect zoom display mode: '.$zoom);
	if($layout=='single' or $layout=='continuous' or $layout=='two' or $layout=='twoleft' or $layout=='tworight' or $layout=='default')
		$this->LayoutMode=$layout;
	else
		$this->Error('Incorrect layout display mode: '.$layout);
}

function SetCompression($compress) {
	//Set page compression
	if(function_exists('gzcompress'))	$this->compress=$compress;
	else $this->compress=false;
}

function SetTitle($title) {
	//Title of document // Arrives as UTF-8
	$this->title = $title;
}

function SetSubject($subject) {
	//Subject of document
	$this->subject= $subject;
}

function SetAuthor($author) {
	//Author of document
	$this->author= $author;
}

function SetKeywords($keywords) {
	//Keywords of document
	$this->keywords= $keywords;
}

function SetCreator($creator) {
	//Creator of document
	$this->creator= $creator;
}


function SetAnchor2Bookmark($x) {
	$this->anchor2Bookmark = $x;
}

function AliasNbPages($alias='{nb}') {
	//Define an alias for total number of pages
	$this->aliasNbPg=$alias;
}

function AliasNbPageGroups($alias='{nbpg}') {
	//Define an alias for total number of pages in a group
	$this->aliasNbPgGp=$alias;
}

function SetAlpha($alpha, $bm='Normal', $return=false, $mode='B') {
// alpha: real value from 0 (transparent) to 1 (opaque)
// bm:    blend mode, one of the following:
//          Normal, Multiply, Screen, Overlay, Darken, Lighten, ColorDodge, ColorBurn,
//          HardLight, SoftLight, Difference, Exclusion, Hue, Saturation, Color, Luminosity
// set alpha for stroking (CA) and non-stroking (ca) operations
// mode determines F (fill) S (stroke) B (both)
	if (($this->PDFA || $this->PDFX) && $alpha!=1) { 
		if (($this->PDFA && !$this->PDFAauto) || ($this->PDFX && !$this->PDFXauto)) { $this->PDFAXwarnings[] = "Image opacity must be 100% (Opacity changed to 100%)"; }
		$alpha = 1; 
	}
	$a = array('BM'=>'/'.$bm);
	if ($mode=='F' || $mode='B') $a['ca'] = $alpha;
	if ($mode=='S' || $mode='B') $a['CA'] = $alpha;
	$gs = $this->AddExtGState($a);
	if ($return) { return sprintf('/GS%d gs', $gs); }
	else { $this->_out(sprintf('/GS%d gs', $gs)); }
}

function AddExtGState($parms) {
	$n = count($this->extgstates);
	// check if graphics state already exists
	for ($i=1; $i<=$n; $i++) {
	  if (count($this->extgstates[$i]['parms']) == count($parms)) {
	    $same = true;
	    foreach($this->extgstates[$i]['parms'] AS $k=>$v) {
		if (!isset($parms[$k]) || $parms[$k] != $v) { $same = false; break; }
	    }
	    if ($same) { return $i; }
	  }
	}
	$n++;
	$this->extgstates[$n]['parms'] = $parms;
	return $n;
}

function SetVisibility($v) {
	if (($this->PDFA || $this->PDFX) && $this->visibility!='visible') { $this->PDFAXwarnings[] = "Cannot set visibility to anything other than full when using PDFA or PDFX"; return ''; }
	else if (!$this->PDFA && !$this->PDFX)
		$this->pdf_version='1.5';
	if($this->visibility!='visible') {
		$this->_out('EMC');
		$this->hasOC=intval($this->hasOC );	// mPDF 5.6.01
	}
	if($v=='printonly') {
		$this->_out('/OC /OC1 BDC');
		$this->hasOC=($this->hasOC | 1);	// mPDF 5.6.01
	}
	elseif($v=='screenonly') {
		$this->_out('/OC /OC2 BDC');
		$this->hasOC=($this->hasOC | 2);	// mPDF 5.6.01
	}
	elseif($v=='hidden') {
		$this->_out('/OC /OC3 BDC');
		$this->hasOC=($this->hasOC | 4);	// mPDF 5.6.01
	}
	elseif($v!='visible')
		$this->Error('Incorrect visibility: '.$v);
	$this->visibility=$v;
}

function Error($msg) {
	//Fatal error
	header('Content-Type: text/html; charset=utf-8');
	die('<B>mPDF error: </B>'.$msg);
}

function Open() {
	//Begin document
	if($this->state==0)	$this->_begindoc();
}

function Close() {
	if ($this->progressBar) { $this->UpdateProgressBar(2,'2','Closing last page'); }	// *PROGRESS-BAR*
	//Terminate document
	if($this->state==3)	return;
	if($this->page==0) $this->AddPage($this->CurOrientation);
	if (count($this->cellBorderBuffer)) { $this->printcellbuffer(); }	// *TABLES*
	if ($this->tablebuffer) { $this->printtablebuffer(); }	// *TABLES*
/*-- COLUMNS --*/

	if ($this->ColActive) {
		$this->SetColumns(0);
		$this->ColActive = 0; 
		if (count($this->columnbuffer)) { $this->printcolumnbuffer(); }
	}
/*-- END COLUMNS --*/
	if (count($this->divbuffer)) { $this->printdivbuffer(); }

	// BODY Backgrounds
	$s = '';

	$s .= $this->PrintBodyBackgrounds();

	$s .= $this->PrintPageBackgrounds();
	$this->pages[$this->page] = preg_replace('/(___BACKGROUND___PATTERNS'.date('jY').')/', "\n".$s."\n".'\\1', $this->pages[$this->page]);
	$this->pageBackgrounds = array();

	if($this->visibility!='visible')
		$this->SetVisibility('visible');
	// mPDF 5.6.01 - LAYERS
	$this->EndLayer();

	if (!$this->tocontents || !$this->tocontents->TOCmark) { //Page footer
		$this->InFooter=true;
		$this->Footer();
		$this->InFooter=false;
	}
	if ($this->tocontents && ($this->tocontents->TOCmark || count($this->tocontents->m_TOC))) { $this->tocontents->insertTOC(); }	// *TOC*

	//Close page
	$this->_endpage();

	//Close document
	$this->_enddoc();
}

/*-- BACKGROUNDS --*/
function _resizeBackgroundImage($imw, $imh, $cw, $ch, $resize=0, $repx, $repy, $pba=array(), $size=array()) {	// mPDF 5.6.10
	// pba is background positioning area (from CSS background-origin) may not always be set [x,y,w,h]
	// size is from CSS3 background-size - takes precendence over old resize 
	//	$w - absolute length or % or auto or cover | contain
	//	$h - absolute length or % or auto or cover | contain
	// mPDF 5.6.10
	if (isset($pba['w'])) $cw = $pba['w'];
	if (isset($pba['h'])) $ch = $pba['h'];

	$cw = $cw*_MPDFK;
	$ch = $ch*_MPDFK;
	if (empty($size) && !$resize) { return array($imw, $imh, $repx, $repy); }

	// mPDF 5.6.10
	if (isset($size['w']) && $size['w']) {
		if ($size['w']=='contain') {
		// Scale the image, while preserving its intrinsic aspect ratio (if any), to the largest size such that both its width and its height can fit inside the background positioning area. 
		// Same as resize==3
			$h = $imh * $cw/$imw;
			$w = $cw;
			if ($h > $ch) {
				$w = $w * $ch/$h;
				$h = $ch;
			}
		}
		else if ($size['w']=='cover') {
		// Scale the image, while preserving its intrinsic aspect ratio (if any), to the smallest size such that both its width and its height can completely cover the background positioning area. 
			$h = $imh * $cw/$imw;
			$w = $cw;
			if ($h < $ch) {
				$w = $w * $h/$ch;
				$h = $ch;
			}
		}
		else {
			if (stristr($size['w'] ,'%')) {
				$size['w'] += 0; 
				$size['w'] /= 100; 
				$size['w'] = ($cw * $size['w']);
			}
			if (stristr($size['h'] ,'%')) {
				$size['h'] += 0; 
				$size['h'] /= 100; 
				$size['h'] = ($ch * $size['h']);
			}
			if ($size['w']=='auto' && $size['h']=='auto') {
				$w = $imw;
				$h = $imh;
			}
			else if ($size['w']=='auto' && $size['h']!='auto') {
				$w = $imw * $size['h']/$imh;
				$h = $size['h'];
			}
			else if ($size['w']!='auto' && $size['h']=='auto') {
				$h = $imh * $size['w']/$imw;
				$w = $size['w'];
			}
			else {
				$w = $size['w'];
				$h = $size['h'];
			}
		}
		return array($w, $h, $repx, $repy); 
	}
	else if ($resize==1 && $imw > $cw) {
		$h = $imh * $cw/$imw;
		return array($cw, $h, $repx, $repy); 
	}
	else if ($resize==2 && $imh > $ch) {
		$w = $imw * $ch/$imh;
		return array($w, $ch, $repx, $repy); 
	}
	else if ($resize==3) {
		$w = $imw;
		$h = $imh;
		if ($w > $cw) {
			$h = $h * $cw/$w;
			$w = $cw;
		}
		if ($h > $ch) {
			$w = $w * $ch/$h;
			$h = $ch;
		}
		return array($w, $h, $repx, $repy); 
	}
	else if ($resize==4) {
		$h = $imh * $cw/$imw;
		return array($cw, $h, $repx, $repy); 
	}
	else if ($resize==5) {
		$w = $imw * $ch/$imh;
		return array($w, $ch, $repx, $repy); 
	}
	else if ($resize==6) {
		return array($cw, $ch, $repx, $repy); 
	}
	return array($imw, $imh, $repx, $repy);
}


function SetBackground(&$properties, &$maxwidth) {
	// mPDF 5.6.10  5.6.11
	if (isset($properties['BACKGROUND-ORIGIN']) && ($properties['BACKGROUND-ORIGIN']=='border-box' || $properties['BACKGROUND-ORIGIN']== 'content-box')) { $origin = $properties['BACKGROUND-ORIGIN']; }
	else { $origin = 'padding-box'; }
	// mPDF 5.6.10
	if (isset($properties['BACKGROUND-SIZE'])) { 
		if (stristr($properties['BACKGROUND-SIZE'] ,'contain') ) { $bsw = $bsh = 'contain'; }
		else if (stristr($properties['BACKGROUND-SIZE'] ,'cover') ) { $bsw = $bsh = 'cover'; }
		else {
			$bsw = $bsh = 'auto';
			$sz = preg_split('/\s+/',trim($properties['BACKGROUND-SIZE']));
			if (count($sz)==2) { $bsw = $sz[0]; $bsh = $sz[1]; }
			else { $bsw = $sz[0]; }
			if (!stristr($bsw ,'%') && !stristr($bsw ,'auto') ) { $bsw = $this->ConvertSize($bsw ,$maxwidth,$this->FontSize); }
			if (!stristr($bsh ,'%') && !stristr($bsh ,'auto') ) { $bsh = $this->ConvertSize($bsh ,$maxwidth,$this->FontSize); }
		}
		$size = array('w'=>$bsw, 'h'=>$bsh);
	}
	   if (preg_match('/(-moz-)*(repeating-)*(linear|radial)-gradient/',$properties['BACKGROUND-IMAGE'])) {
		return array('gradient'=>$properties['BACKGROUND-IMAGE'], 'origin'=>$origin, 'size'=>$size );	// mPDF 5.6.10
	   }
	   else {
		$file = $properties['BACKGROUND-IMAGE'];
		$sizesarray = $this->Image($file,0,0,0,0,'','',false, false, false, false, true);
		if (isset($sizesarray['IMAGE_ID'])) {
			$image_id = $sizesarray['IMAGE_ID'];
			$orig_w = $sizesarray['WIDTH']*_MPDFK;		// in user units i.e. mm
 			$orig_h = $sizesarray['HEIGHT']*_MPDFK;		// (using $this->img_dpi)
			if (isset($properties['BACKGROUND-IMAGE-RESOLUTION'])) { 
				if (preg_match('/from-image/i', $properties['BACKGROUND-IMAGE-RESOLUTION']) && isset($sizesarray['set-dpi']) && $sizesarray['set-dpi']>0) {
					$orig_w *= $this->img_dpi / $sizesarray['set-dpi'];
					$orig_h *= $this->img_dpi / $sizesarray['set-dpi'];
				}
				else if (preg_match('/(\d+)dpi/i', $properties['BACKGROUND-IMAGE-RESOLUTION'], $m)) {
					$dpi = $m[1]; 
					if ($dpi > 0) {
						$orig_w *= $this->img_dpi / $dpi;
						$orig_h *= $this->img_dpi / $dpi;
					}
				}
			}
			$x_repeat = true;
			$y_repeat = true;
			if (isset($properties['BACKGROUND-REPEAT'])) {
				if ($properties['BACKGROUND-REPEAT']=='no-repeat' || $properties['BACKGROUND-REPEAT']=='repeat-x') { $y_repeat = false; }
				if ($properties['BACKGROUND-REPEAT']=='no-repeat' || $properties['BACKGROUND-REPEAT']=='repeat-y') { $x_repeat = false; }
			}
			$x_pos = 0;
			$y_pos = 0;
			if (isset($properties['BACKGROUND-POSITION'])) { 
				$ppos = preg_split('/\s+/',$properties['BACKGROUND-POSITION']);
				$x_pos = $ppos[0];
				$y_pos = $ppos[1];
				if (!stristr($x_pos ,'%') ) { $x_pos = $this->ConvertSize($x_pos ,$maxwidth,$this->FontSize); }
				if (!stristr($y_pos ,'%') ) { $y_pos = $this->ConvertSize($y_pos ,$maxwidth,$this->FontSize); }
			}
			if (isset($properties['BACKGROUND-IMAGE-RESIZE'])) { $resize = $properties['BACKGROUND-IMAGE-RESIZE']; }
			else { $resize = 0; }
			if (isset($properties['BACKGROUND-IMAGE-OPACITY'])) { $opacity = $properties['BACKGROUND-IMAGE-OPACITY']; }
			else { $opacity = 1; }
			return array('image_id'=>$image_id, 'orig_w'=>$orig_w, 'orig_h'=>$orig_h, 'x_pos'=>$x_pos, 'y_pos'=>$y_pos, 'x_repeat'=>$x_repeat, 'y_repeat'=>$y_repeat, 'resize'=>$resize, 'opacity'=>$opacity, 'itype'=>$sizesarray['itype'], 'origin'=>$origin, 'size'=>$size );
		}
	   }
	   return false;
}
/*-- END BACKGROUNDS --*/

function PrintBodyBackgrounds() {
	$s = '';
	$clx = 0;
	$cly = 0;
	$clw = $this->w;
	$clh = $this->h;
	// If using bleed and trim margins in paged media
	if ($this->pageDim[$this->page]['outer_width_LR'] || $this->pageDim[$this->page]['outer_width_TB']) {
		$clx = $this->pageDim[$this->page]['outer_width_LR'] - $this->pageDim[$this->page]['bleedMargin'];
		$cly = $this->pageDim[$this->page]['outer_width_TB'] - $this->pageDim[$this->page]['bleedMargin'];
		$clw = $this->w - 2*$clx;
		$clh = $this->h - 2*$cly;
	}

	if ($this->bodyBackgroundColor) {
		$s .= 'q ' .$this->SetFColor($this->bodyBackgroundColor, true)."\n";
		if ($this->bodyBackgroundColor{0}==5) {	// RGBa
			$s .= $this->SetAlpha(ord($this->bodyBackgroundColor{4})/100, 'Normal', true, 'F')."\n";
		}
		else if ($this->bodyBackgroundColor{0}==6) {	// CMYKa
			$s .= $this->SetAlpha(ord($this->bodyBackgroundColor{5})/100, 'Normal', true, 'F')."\n";
		}
		$s .= sprintf('%.3F %.3F %.3F %.3F re f Q', ($clx*_MPDFK), ($cly*_MPDFK),$clw*_MPDFK,$clh*_MPDFK)."\n";
	}

/*-- BACKGROUNDS --*/
	if ($this->bodyBackgroundGradient) { 
		$g = $this->grad->parseBackgroundGradient($this->bodyBackgroundGradient);
		if ($g) {
			$s .= $this->grad->Gradient($clx, $cly, $clw, $clh, (isset($g['gradtype']) ? $g['gradtype'] : null), $g['stops'], $g['colorspace'], $g['coords'], $g['extend'], true);
		}
	}
	if ($this->bodyBackgroundImage) {
	   if ( $this->bodyBackgroundImage['gradient'] && preg_match('/(-moz-)*(repeating-)*(linear|radial)-gradient/', $this->bodyBackgroundImage['gradient'])) {
		$g = $this->grad->parseMozGradient( $this->bodyBackgroundImage['gradient']);
		if ($g) {
			$s .= $this->grad->Gradient($clx, $cly, $clw, $clh, $g['type'], $g['stops'], $g['colorspace'], $g['coords'], $g['extend'], true);
		}
	   }
	   else if ($this->bodyBackgroundImage['image_id']) {	// Background pattern
			$n = count($this->patterns)+1;
			// If using resize, uses TrimBox (not including the bleed)
			list($orig_w, $orig_h, $x_repeat, $y_repeat) = $this->_resizeBackgroundImage($this->bodyBackgroundImage['orig_w'], $this->bodyBackgroundImage['orig_h'], $clw, $clh, $this->bodyBackgroundImage['resize'], $this->bodyBackgroundImage['x_repeat'], $this->bodyBackgroundImage['y_repeat']);

			$this->patterns[$n] = array('x'=>$clx, 'y'=>$cly, 'w'=>$clw, 'h'=>$clh, 'pgh'=>$this->h, 'image_id'=>$this->bodyBackgroundImage['image_id'], 'orig_w'=>$orig_w, 'orig_h'=>$orig_h, 'x_pos'=>$this->bodyBackgroundImage['x_pos'], 'y_pos'=>$this->bodyBackgroundImage['y_pos'], 'x_repeat'=>$x_repeat, 'y_repeat'=>$y_repeat, 'itype'=>$this->bodyBackgroundImage['itype']);
			if (($this->bodyBackgroundImage['opacity']>0 || $this->bodyBackgroundImage['opacity']==='0') && $this->bodyBackgroundImage['opacity']<1) { $opac = $this->SetAlpha($this->bodyBackgroundImage['opacity'],'Normal',true); }
			else { $opac = ''; }
			$s .= sprintf('q /Pattern cs /P%d scn %s %.3F %.3F %.3F %.3F re f Q', $n, $opac, ($clx*_MPDFK), ($cly*_MPDFK),$clw*_MPDFK, $clh*_MPDFK) ."\n";
	   }
	}
/*-- END BACKGROUNDS --*/
	return $s;
}


function PrintPageBackgrounds($adjustmenty=0) {
	$s = '';

	ksort($this->pageBackgrounds);
	foreach($this->pageBackgrounds AS $bl=>$pbs) {
		foreach ($pbs AS $pb) {
		  if ((!isset($pb['image_id']) && !isset($pb['gradient'])) || isset($pb['shadowonly'])) {	// Background colour or boxshadow
			// mPDF 5.6.01  - LAYERS
			if($pb['z-index']>0) {
				$this->current_layer = $pb['z-index'];
				$s .= "\n".'/OCBZ-index /ZI'.$pb['z-index'].' BDC'."\n";
			}

			if($pb['visibility']!='visible') {
				if($pb['visibility']=='printonly') 
					$s .= '/OC /OC1 BDC'."\n";
				else if($pb['visibility']=='screenonly')
					$s .= '/OC /OC2 BDC'."\n";
				else if($pb['visibility']=='hidden')
					$s .= '/OC /OC3 BDC'."\n";
			}
			// Box shadow
			if (isset($pb['shadow']) && $pb['shadow']) { $s .= $pb['shadow']."\n"; }
			if (isset($pb['clippath']) && $pb['clippath']) { $s .= $pb['clippath']."\n"; }
			$s .= 'q '.$this->SetFColor($pb['col'], true)."\n";
			if ($pb['col']{0}==5) {	// RGBa
				$s .= $this->SetAlpha(ord($pb['col']{4})/100, 'Normal', true, 'F')."\n"; 
			}
			else if ($pb['col']{0}==6) {	// CMYKa
				$s .= $this->SetAlpha(ord($pb['col']{5})/100, 'Normal', true, 'F')."\n";
			}
			$s .= sprintf('%.3F %.3F %.3F %.3F re f Q',$pb['x']*_MPDFK,($this->h-$pb['y'])*_MPDFK,$pb['w']*_MPDFK,-$pb['h']*_MPDFK)."\n";
			if (isset($pb['clippath']) && $pb['clippath']) { $s .= 'Q'."\n"; }
			if($pb['visibility']!='visible')
				$s .= 'EMC'."\n";

			// mPDF 5.6.01  - LAYERS
			if($pb['z-index']>0) {
				$s .= "\n".'EMCBZ-index'."\n";
				$this->current_layer = 0;
			}
		  }
		}
/*-- BACKGROUNDS --*/
		foreach ($pbs AS $pb) {
		  // mPDF 5.6.01  - LAYERS
	 	  if ((isset($pb['gradient']) && $pb['gradient']) || (isset($pb['image_id']) && $pb['image_id'])) {
		 	if($pb['z-index']>0) {
				$this->current_layer = $pb['z-index'];
				$s .= "\n".'/OCGZ-index /ZI'.$pb['z-index'].' BDC'."\n";
		 	}
		 	if($pb['visibility']!='visible') {
				if($pb['visibility']=='printonly') 
					$s .= '/OC /OC1 BDC'."\n";
				else if($pb['visibility']=='screenonly')
					$s .= '/OC /OC2 BDC'."\n";
				else if($pb['visibility']=='hidden')
					$s .= '/OC /OC3 BDC'."\n";
		 	}
		  }
	 	  if (isset($pb['gradient']) && $pb['gradient']) {
			if (isset($pb['clippath']) && $pb['clippath']) { $s .= $pb['clippath']."\n"; }
			$s .= $this->grad->Gradient($pb['x'], $pb['y'], $pb['w'], $pb['h'], $pb['gradtype'], $pb['stops'], $pb['colorspace'], $pb['coords'], $pb['extend'], true);
			if (isset($pb['clippath']) && $pb['clippath']) { $s .= 'Q'."\n"; }
		  }
		  else if (isset($pb['image_id']) && $pb['image_id']) {	// Background Image
			$pb['y'] -= $adjustmenty; 
			$pb['h'] += $adjustmenty; 
			$n = count($this->patterns)+1;
			list($orig_w, $orig_h, $x_repeat, $y_repeat) = $this->_resizeBackgroundImage($pb['orig_w'], $pb['orig_h'], $pb['w'], $pb['h'], $pb['resize'], $pb['x_repeat'], $pb['y_repeat'], $pb['bpa'], $pb['size']);	// mPDF 5.6.10
			$this->patterns[$n] = array('x'=>$pb['x'], 'y'=>$pb['y'], 'w'=>$pb['w'], 'h'=>$pb['h'], 'pgh'=>$this->h, 'image_id'=>$pb['image_id'], 'orig_w'=>$orig_w, 'orig_h'=>$orig_h, 'x_pos'=>$pb['x_pos'], 'y_pos'=>$pb['y_pos'], 'x_repeat'=>$x_repeat, 'y_repeat'=>$y_repeat, 'itype'=>$pb['itype'], 'bpa'=>$pb['bpa']);	// mPDF 5.6.10
			$x = $pb['x']*_MPDFK;
			$y = ($this->h - $pb['y'])*_MPDFK;
			$w = $pb['w']*_MPDFK;
			$h = -$pb['h']*_MPDFK;
			if (isset($pb['clippath']) && $pb['clippath']) { $s .= $pb['clippath']."\n"; }
			if ($this->writingHTMLfooter || $this->writingHTMLheader) {	// Write each (tiles) image rather than use as a pattern
				$iw = $pb['orig_w']/_MPDFK;
				$ih = $pb['orig_h']/_MPDFK;

				$w = $pb['w'];
				$h = $pb['h'];
				$x0 = $pb['x'];
				$y0 = $pb['y'];

				// mPDF 5.6.11
				if (isset($pb['bpa']) && $pb['bpa']) {
					$w = $pb['bpa']['w'];
					$h = $pb['bpa']['h'];
					$x0 = $pb['bpa']['x'];
					$y0 = $pb['bpa']['y'];
				}

				// mPDF 5.6.11
				if (isset($pb['size']['w']) && $pb['size']['w']) {
					$size = $pb['size'];

					if ($size['w']=='contain') {
					// Scale the image, while preserving its intrinsic aspect ratio (if any), to the largest size such that both its width and its height can fit inside the background positioning area. 
					// Same as resize==3
						$ih = $ih * $pb['bpa']['w']/$iw;
						$iw = $pb['bpa']['w'];
						if ($ih > $pb['bpa']['h']) {
							$iw = $iw * $pb['bpa']['h']/$ih;
							$ih = $pb['bpa']['h'];
						}
					}
					else if ($size['w']=='cover') {
					// Scale the image, while preserving its intrinsic aspect ratio (if any), to the smallest size such that both its width and its height can completely cover the background positioning area. 
						$ih = $ih * $pb['bpa']['w']/$iw;
						$iw = $pb['bpa']['w'];
						if ($ih < $pb['bpa']['h']) {
							$iw = $iw * $ih/$pb['bpa']['h'];
							$ih = $pb['bpa']['h'];
						}
					}
					else {
						if (stristr($size['w'] ,'%')) {
							$size['w'] += 0; 
							$size['w'] /= 100; 
							$size['w'] = ($pb['bpa']['w'] * $size['w']);
						}
						if (stristr($size['h'] ,'%')) {
							$size['h'] += 0; 
							$size['h'] /= 100; 
							$size['h'] = ($pb['bpa']['h'] * $size['h']);
						}
						if ($size['w']=='auto' && $size['h']=='auto') {
							$iw = $iw;
							$ih = $ih;
						}
						else if ($size['w']=='auto' && $size['h']!='auto') {
							$iw = $iw * $size['h']/$ih;
							$ih = $size['h'];
						}
						else if ($size['w']!='auto' && $size['h']=='auto') {
							$ih = $ih * $size['w']/$iw;
							$iw = $size['w'];
						}
						else {
							$iw = $size['w'];
							$ih = $size['h'];
						}
					}
				}

				// Number to repeat
				if ($pb['x_repeat']) { $nx = ceil($pb['w']/$iw)+1; } 	// mPDF 5.6.11
				else { $nx = 1; }
				if ($pb['y_repeat']) { $ny = ceil($pb['h']/$ih)+1; } 	// mPDF 5.6.11
				else { $ny = 1; }

				$x_pos = $pb['x_pos'];
				if (stristr($x_pos ,'%') ) { 
					$x_pos += 0; 
					$x_pos /= 100; 
					$x_pos = ($pb['bpa']['w'] * $x_pos) - ($iw * $x_pos); 	// mPDF 5.6.11
				}
				$y_pos = $pb['y_pos'];
				if (stristr($y_pos ,'%') ) { 
					$y_pos += 0; 
					$y_pos /= 100; 
					$y_pos = ($pb['bpa']['h'] * $y_pos) - ($ih * $y_pos); 	// mPDF 5.6.11
				}
				if ($nx>1) {
					while($x_pos>($pb['x']-$pb['bpa']['x'])) { $x_pos -= $iw; }	// mPDF 5.6.11
				}
				if ($ny>1) {
					while($y_pos>($pb['y']-$pb['bpa']['y'])) { $y_pos -= $ih; }	// mPDF 5.6.11
				}
				for($xi=0;$xi<$nx;$xi++) {
				  for($yi=0;$yi<$ny;$yi++) {
					$x = $x0 + $x_pos + ($iw*$xi);
					$y = $y0 + $y_pos + ($ih*$yi);
					if ($pb['opacity']>0 && $pb['opacity']<1) { $opac = $this->SetAlpha($pb['opacity'],'Normal',true); }
					else { $opac = ''; }
					$s .= sprintf("q %s %.3F 0 0 %.3F %.3F %.3F cm /I%d Do Q", $opac,$iw*_MPDFK,$ih*_MPDFK,$x*_MPDFK,($this->h-($y+$ih))*_MPDFK,$pb['image_id']) ."\n";
				  }
				}
			}
			else {
				if (($pb['opacity']>0 || $pb['opacity']==='0') && $pb['opacity']<1) { $opac = $this->SetAlpha($pb['opacity'],'Normal',true); }
				else { $opac = ''; }
				$s .= sprintf('q /Pattern cs /P%d scn %s %.3F %.3F %.3F %.3F re f Q', $n, $opac, $x, $y, $w, $h) ."\n";
			}
			if (isset($pb['clippath']) && $pb['clippath']) { $s .= 'Q'."\n"; }
		  }
	 	  if ((isset($pb['gradient']) && $pb['gradient']) || (isset($pb['image_id']) && $pb['image_id'])) {
		 	if($pb['visibility']!='visible')
				$s .= 'EMC'."\n";

			// mPDF 5.6.01  - LAYERS
			if($pb['z-index']>0) {
				$s .= "\n".'EMCGZ-index'."\n";
				$this->current_layer = 0;
			}
		  }

		}
/*-- END BACKGROUNDS --*/
	}
	return $s;
}

function PrintTableBackgrounds($adjustmenty=0) {
	$s = '';
/*-- BACKGROUNDS --*/
	ksort($this->tableBackgrounds);
	foreach($this->tableBackgrounds AS $bl=>$pbs) {
		foreach ($pbs AS $pb) {
	 	 if ((!isset($pb['gradient']) || !$pb['gradient']) && (!isset($pb['image_id']) || !$pb['image_id'])) {
			$s .= 'q '.$this->SetFColor($pb['col'], true)."\n";
			if ($pb['col']{0}==5) {	// RGBa
				$s .= $this->SetAlpha(ord($pb['col']{4})/100, 'Normal', true, 'F')."\n"; 
			}
			else if ($pb['col']{0}==6) {	// CMYKa
				$s .= $this->SetAlpha(ord($pb['col']{5})/100, 'Normal', true, 'F')."\n";
			}
			$s .= sprintf('%.3F %.3F %.3F %.3F re %s Q',$pb['x']*_MPDFK,($this->h-$pb['y'])*_MPDFK,$pb['w']*_MPDFK,-$pb['h']*_MPDFK,'f')."\n";
		  }
	 	 if (isset($pb['gradient']) && $pb['gradient']) {
			if (isset($pb['clippath']) && $pb['clippath']) { $s .= $pb['clippath']."\n"; }
			$s .= $this->grad->Gradient($pb['x'], $pb['y'], $pb['w'], $pb['h'], $pb['gradtype'], $pb['stops'], $pb['colorspace'], $pb['coords'], $pb['extend'], true);
			if (isset($pb['clippath']) && $pb['clippath']) { $s .= 'Q'."\n"; }
		  }
		  if (isset($pb['image_id']) && $pb['image_id']) {	// Background pattern
			$pb['y'] -= $adjustmenty; 
			$pb['h'] += $adjustmenty; 
			$n = count($this->patterns)+1;
			list($orig_w, $orig_h, $x_repeat, $y_repeat) = $this->_resizeBackgroundImage($pb['orig_w'], $pb['orig_h'], $pb['w'], $pb['h'], $pb['resize'], $pb['x_repeat'], $pb['y_repeat']);
			$this->patterns[$n] = array('x'=>$pb['x'], 'y'=>$pb['y'], 'w'=>$pb['w'], 'h'=>$pb['h'], 'pgh'=>$this->h, 'image_id'=>$pb['image_id'], 'orig_w'=>$orig_w, 'orig_h'=>$orig_h, 'x_pos'=>$pb['x_pos'], 'y_pos'=>$pb['y_pos'], 'x_repeat'=>$x_repeat, 'y_repeat'=>$y_repeat, 'itype'=>$pb['itype']);
			$x = $pb['x']*_MPDFK;
			$y = ($this->h - $pb['y'])*_MPDFK;
			$w = $pb['w']*_MPDFK;
			$h = -$pb['h']*_MPDFK;
			if (isset($pb['clippath']) && $pb['clippath']) { $s .= $pb['clippath']."\n"; }
			if ($pb['opacity']>0 && $pb['opacity']<1) { $opac = $this->SetAlpha($pb['opacity'],'Normal',true); }
			else { $opac = ''; }
			$s .= sprintf('q /Pattern cs /P%d scn %s %.3F %.3F %.3F %.3F re f Q', $n, $opac, $x, $y, $w, $h) ."\n";
			if (isset($pb['clippath']) && $pb['clippath']) { $s .= 'Q'."\n"; }
		  }
		}
	}
/*-- END BACKGROUNDS --*/
	return $s;
}

// mPDF 5.6.01 - LAYERS
function BeginLayer($id) {
	if($this->current_layer>0) $this->EndLayer();
	if ($id < 1) { return false; }
	if (!isset($this->layers[$id])) { 
		$this->layers[$id] = array('name'=>'Layer '.($id) );
		if (($this->PDFA || $this->PDFX)) { $this->PDFAXwarnings[] = "Cannot use layers when using PDFA or PDFX"; return ''; }
		else if (!$this->PDFA && !$this->PDFX) { $this->pdf_version='1.5'; }
	}
	$this->current_layer = $id;
	$this->_out('/OCZ-index /ZI'.$id.' BDC');

	$this->pageoutput[$this->page] = array();
}

function EndLayer() {
	if($this->current_layer>0) {
		$this->_out('EMCZ-index');
		$this->current_layer = 0;
	}
}



// Depracated - can use AddPage for all
function AddPages($orientation='',$condition='', $resetpagenum='', $pagenumstyle='', $suppress='',$mgl='',$mgr='',$mgt='',$mgb='',$mgh='',$mgf='',$ohname='',$ehname='',$ofname='',$efname='',$ohvalue=0,$ehvalue=0,$ofvalue=0,$efvalue=0,$pagesel='',$newformat='')
{
	$this->AddPage($orientation,$condition,$resetpagenum, $pagenumstyle, $suppress,$mgl,$mgr,$mgt,$mgb,$mgh,$mgf, $ohname, $ehname, $ofname, $efname, $ohvalue, $ehvalue, $ofvalue, $efvalue,$pagesel,$newformat); 
}


function AddPageByArray($a) {
	if (!is_array($a)) { $a = array(); }
	$orientation = (isset($a['orientation']) ? $a['orientation'] : '');
	$condition = (isset($a['condition']) ? $a['condition'] : (isset($a['type']) ? $a['type'] : ''));
	$resetpagenum = (isset($a['resetpagenum']) ? $a['resetpagenum'] : '');
	$pagenumstyle = (isset($a['pagenumstyle']) ? $a['pagenumstyle'] : '');
	$suppress = (isset($a['suppress']) ? $a['suppress'] : '');
	$mgl = (isset($a['mgl']) ? $a['mgl'] : (isset($a['margin-left']) ? $a['margin-left'] : ''));
	$mgr = (isset($a['mgr']) ? $a['mgr'] : (isset($a['margin-right']) ? $a['margin-right'] : ''));
	$mgt = (isset($a['mgt']) ? $a['mgt'] : (isset($a['margin-top']) ? $a['margin-top'] : ''));
	$mgb = (isset($a['mgb']) ? $a['mgb'] : (isset($a['margin-bottom']) ? $a['margin-bottom'] : ''));
	$mgh = (isset($a['mgh']) ? $a['mgh'] : (isset($a['margin-header']) ? $a['margin-header'] : ''));
	$mgf = (isset($a['mgf']) ? $a['mgf'] : (isset($a['margin-footer']) ? $a['margin-footer'] : ''));
	$ohname = (isset($a['ohname']) ? $a['ohname'] : (isset($a['odd-header-name']) ? $a['odd-header-name'] : ''));
	$ehname = (isset($a['ehname']) ? $a['ehname'] : (isset($a['even-header-name']) ? $a['even-header-name'] : ''));
	$ofname = (isset($a['ofname']) ? $a['ofname'] : (isset($a['odd-footer-name']) ? $a['odd-footer-name'] : ''));
	$efname = (isset($a['efname']) ? $a['efname'] : (isset($a['even-footer-name']) ? $a['even-footer-name'] : ''));
	$ohvalue = (isset($a['ohvalue']) ? $a['ohvalue'] : (isset($a['odd-header-value']) ? $a['odd-header-value'] : 0));
	$ehvalue = (isset($a['ehvalue']) ? $a['ehvalue'] : (isset($a['even-header-value']) ? $a['even-header-value'] : 0));
	$ofvalue = (isset($a['ofvalue']) ? $a['ofvalue'] : (isset($a['odd-footer-value']) ? $a['odd-footer-value'] : 0));
	$efvalue = (isset($a['efvalue']) ? $a['efvalue'] : (isset($a['even-footer-value']) ? $a['even-footer-value'] : 0));
	$pagesel = (isset($a['pagesel']) ? $a['pagesel'] : (isset($a['pageselector']) ? $a['pageselector'] : ''));
	$newformat = (isset($a['newformat']) ? $a['newformat'] : (isset($a['sheet-size']) ? $a['sheet-size'] : ''));

	$this->AddPage($orientation,$condition,$resetpagenum, $pagenumstyle, $suppress,$mgl,$mgr,$mgt,$mgb,$mgh,$mgf, $ohname, $ehname, $ofname, $efname, $ohvalue, $ehvalue, $ofvalue, $efvalue,$pagesel,$newformat);

}


function AddPage($orientation='',$condition='', $resetpagenum='', $pagenumstyle='', $suppress='',$mgl='',$mgr='',$mgt='',$mgb='',$mgh='',$mgf='',$ohname='',$ehname='',$ofname='',$efname='',$ohvalue=0,$ehvalue=0,$ofvalue=0,$efvalue=0,$pagesel='',$newformat='')
{
/*-- CSS-FLOAT --*/
	// Float DIV
	// Cannot do with columns on, or if any change in page orientation/margins etc.
	// If next page already exists - i.e background /headers and footers already written
	if ($this->state > 0 && $this->page < count($this->pages)) {
		$bak_cml = $this->cMarginL;
		$bak_cmr = $this->cMarginR; 
		$bak_dw = $this->divwidth;
		// Paint Div Border if necessary
   		if ($this->blklvl > 0) {
			$save_tr = $this->table_rotate;	// *TABLES*
			$this->table_rotate = 0;	// *TABLES*
			if ($this->y == $this->blk[$this->blklvl]['y0']) {  $this->blk[$this->blklvl]['startpage']++; }
			if (($this->y > $this->blk[$this->blklvl]['y0']) || $this->flowingBlockAttr['is_table'] ) { $toplvl = $this->blklvl; }
			else { $toplvl = $this->blklvl-1; }
			$sy = $this->y;
			for ($bl=1;$bl<=$toplvl;$bl++) {
				$this->PaintDivBB('pagebottom',0,$bl);
			}
			$this->y = $sy;
			$this->table_rotate = $save_tr;	// *TABLES*
		}
		$s = $this->PrintPageBackgrounds();

		// Writes after the marker so not overwritten later by page background etc.
		$this->pages[$this->page] = preg_replace('/(___BACKGROUND___PATTERNS'.date('jY').')/', '\\1'."\n".$s."\n", $this->pages[$this->page]);
		$this->pageBackgrounds = array();
		$family=$this->FontFamily;
		$style=$this->FontStyle.($this->U ? 'U' : '').($this->S ? 'S' : '');
		$size=$this->FontSizePt;
		$lw=$this->LineWidth;
		$dc=$this->DrawColor;
		$fc=$this->FillColor;
		$tc=$this->TextColor;
		$cf=$this->ColorFlag;

		$this->printfloatbuffer();

		//Move to next page
		$this->page++;
		$this->ResetMargins();
		$this->SetAutoPageBreak($this->autoPageBreak,$this->bMargin);
		$this->x=$this->lMargin;
		$this->y=$this->tMargin;
		$this->FontFamily='';
		$this->_out('2 J');
		$this->LineWidth=$lw;
		$this->_out(sprintf('%.3F w',$lw*_MPDFK));
		if($family)	$this->SetFont($family,$style,$size,true,true);
		$this->DrawColor=$dc;
		if($dc!=$this->defDrawColor) $this->_out($dc);
		$this->FillColor=$fc;
		if($fc!=$this->defFillColor) $this->_out($fc);
		$this->TextColor=$tc;
		$this->ColorFlag=$cf;
		for($bl=1;$bl<=$this->blklvl;$bl++) {
			$this->blk[$bl]['y0'] = $this->y;
			// Don't correct more than once for background DIV containing a Float
			if (!isset($this->blk[$bl]['marginCorrected'][$this->page])) { $this->blk[$bl]['x0'] += $this->MarginCorrection; }
			$this->blk[$bl]['marginCorrected'][$this->page] = true; 
		}
		$this->cMarginL = $bak_cml;
		$this->cMarginR = $bak_cmr;
		$this->divwidth = $bak_dw;
		return '';
	}
/*-- END CSS-FLOAT --*/

	//Start a new page
	if($this->state==0) $this->Open();

	$bak_cml = $this->cMarginL;
	$bak_cmr = $this->cMarginR; 
	$bak_dw = $this->divwidth;


	$bak_lh = $this->lineheight;

	$orientation = substr(strtoupper($orientation),0,1);
	$condition = strtoupper($condition);


	if ($condition == 'NEXT-EVEN') {	// always adds at least one new page to create an Even page
	   if (!$this->mirrorMargins) { $condition = ''; }
	   else { 
		if ($pagesel) { $pbch = $pagesel; $pagesel = ''; }	// *CSS-PAGE*
		else { $pbch = false; }	// *CSS-PAGE*
		$this->AddPage($this->CurOrientation,'O'); 
		if ($pbch ) { $pagesel = $pbch; }	// *CSS-PAGE*
		$condition = ''; 
	   }
	}
	if ($condition == 'NEXT-ODD') {	// always adds at least one new page to create an Odd page
	   if (!$this->mirrorMargins) { $condition = ''; }
	   else { 
		if ($pagesel) { $pbch = $pagesel; $pagesel = ''; }	// *CSS-PAGE*
		else { $pbch = false; }	// *CSS-PAGE*
		$this->AddPage($this->CurOrientation,'E'); 
		if ($pbch ) { $pagesel = $pbch; }	// *CSS-PAGE*
		$condition = ''; 
	   }
	}


	if ($condition == 'E') {	// only adds new page if needed to create an Even page
	   if (!$this->mirrorMargins || ($this->page)%2==0) { return false; }
	}
	if ($condition == 'O') {	// only adds new page if needed to create an Odd page
	   if (!$this->mirrorMargins || ($this->page)%2==1) { return false; }
	}

	if ($resetpagenum || $pagenumstyle || $suppress) {
		$this->PageNumSubstitutions[] = array('from'=>($this->page+1), 'reset'=> $resetpagenum, 'type'=>$pagenumstyle, 'suppress'=>$suppress);
	}


	$save_tr = $this->table_rotate;	// *TABLES*
	$this->table_rotate = 0;	// *TABLES*
	$save_kwt = $this->kwt;
	$this->kwt = 0;
	// mPDF 5.6.01 - LAYERS
	$save_layer = $this->current_layer;
	$save_vis = $this->visibility;

	if($this->visibility!='visible')
		$this->SetVisibility('visible');
	// mPDF 5.6.01 - LAYERS
	$this->EndLayer();

	// Paint Div Border if necessary
   	//PAINTS BACKGROUND COLOUR OR BORDERS for DIV - DISABLED FOR COLUMNS (cf. AcceptPageBreak) AT PRESENT in ->PaintDivBB
   	if (!$this->ColActive && $this->blklvl > 0) {
		if (isset($this->blk[$this->blklvl]['y0']) && $this->y == $this->blk[$this->blklvl]['y0']) {  
			if (isset($this->blk[$this->blklvl]['startpage'])) { $this->blk[$this->blklvl]['startpage']++; }
			else { $this->blk[$this->blklvl]['startpage'] = 1; }
		}
		if ((isset($this->blk[$this->blklvl]['y0']) && $this->y > $this->blk[$this->blklvl]['y0']) || $this->flowingBlockAttr['is_table'] ) { $toplvl = $this->blklvl; }
		else { $toplvl = $this->blklvl-1; }
		$sy = $this->y;
		for ($bl=1;$bl<=$toplvl;$bl++) {

			// mPDF 5.6.01 - LAYERS
			if ($this->blk[$bl]['z-index']>0) {
				$this->BeginLayer($this->blk[$bl]['z-index']);
			}
			if (isset($this->blk[$bl]['visibility']) && $this->blk[$bl]['visibility'] && $this->blk[$bl]['visibility']!='visible') {
				$this->SetVisibility($this->blk[$bl]['visibility']);
			}

			$this->PaintDivBB('pagebottom',0,$bl);
		}
		$this->y = $sy;
		// RESET block y0 and x0 - see below
	}

	if($this->visibility!='visible')
		$this->SetVisibility('visible');
	// mPDF 5.6.01 - LAYERS
	$this->EndLayer();

	// BODY Backgrounds
	if ($this->page > 0) {
		$s = '';
		$s .= $this->PrintBodyBackgrounds();

		$s .= $this->PrintPageBackgrounds();
		$this->pages[$this->page] = preg_replace('/(___BACKGROUND___PATTERNS'.date('jY').')/', "\n".$s."\n".'\\1', $this->pages[$this->page]);
		$this->pageBackgrounds = array();
	}

	$save_kt = $this->keep_block_together;
	$this->keep_block_together = 0;

	$save_cols = false;
/*-- COLUMNS --*/
	if ($this->ColActive) {
		$save_cols = true;
		$save_nbcol = $this->NbCol;	// other values of gap and vAlign will not change by setting Columns off
		$this->SetColumns(0);
	}
/*-- END COLUMNS --*/


	$family=$this->FontFamily;
	$style=$this->FontStyle.($this->U ? 'U' : '').($this->S ? 'S' : '');
	$size=$this->FontSizePt;
	$this->ColumnAdjust = true;	// enables column height adjustment for the page
	$lw=$this->LineWidth;
	$dc=$this->DrawColor;
	$fc=$this->FillColor;
	$tc=$this->TextColor;
	$cf=$this->ColorFlag;
	if($this->page>0)
	{
		//Page footer
		$this->InFooter=true;

		$this->Reset();
		$this->pageoutput[$this->page] = array();

		$this->Footer();
		//Close page
		$this->_endpage();
	}


	//Start new page
	$this->_beginpage($orientation,$mgl,$mgr,$mgt,$mgb,$mgh,$mgf,$ohname,$ehname,$ofname,$efname,$ohvalue,$ehvalue,$ofvalue,$efvalue,$pagesel,$newformat);
	if ($this->docTemplate) {
		$pagecount = $this->SetSourceFile($this->docTemplate);
		if (($this->page - $this->docTemplateStart) > $pagecount) {
			if ($this->docTemplateContinue) { 
				$tplIdx = $this->ImportPage($pagecount);
				$this->UseTemplate($tplIdx);
			}
		}
		else {
			$tplIdx = $this->ImportPage(($this->page - $this->docTemplateStart));
			$this->UseTemplate($tplIdx);
		}
	}
	if ($this->pageTemplate) {
		$this->UseTemplate($this->pageTemplate);
	}

	// Tiling Patterns
	$this->_out('___PAGE___START'.date('jY'));
	$this->_out('___BACKGROUND___PATTERNS'.date('jY'));
	$this->_out('___HEADER___MARKER'.date('jY'));
	$this->pageBackgrounds = array();

	//Set line cap style to square
	$this->SetLineCap(2);
	//Set line width
	$this->LineWidth=$lw;
	$this->_out(sprintf('%.3F w',$lw*_MPDFK));
	//Set font
	if($family)	$this->SetFont($family,$style,$size,true,true);	// forces write
	//Set colors
	$this->DrawColor=$dc;
	if($dc!=$this->defDrawColor) $this->_out($dc);
	$this->FillColor=$fc;
	if($fc!=$this->defFillColor) $this->_out($fc);
	$this->TextColor=$tc;
	$this->ColorFlag=$cf;

	//Page header
	$this->Header();

	//Restore line width
	if($this->LineWidth!=$lw)
	{
		$this->LineWidth=$lw;
		$this->_out(sprintf('%.3F w',$lw*_MPDFK));
	}
	//Restore font
	if($family)	$this->SetFont($family,$style,$size,true,true);	// forces write
	//Restore colors
	if($this->DrawColor!=$dc)
	{
		$this->DrawColor=$dc;
		$this->_out($dc);
	}
	if($this->FillColor!=$fc)
	{
		$this->FillColor=$fc;
		$this->_out($fc);
	}
	$this->TextColor=$tc;
	$this->ColorFlag=$cf;
 	$this->InFooter=false;

	// mPDF 5.6.01 - LAYERS
	if ($save_layer>0)
		$this->BeginLayer($save_layer);

	if($save_vis!='visible')
		$this->SetVisibility($save_vis);

/*-- COLUMNS --*/
	if ($save_cols) {
		// Restore columns
		$this->SetColumns($save_nbcol,$this->colvAlign,$this->ColGap);
	}
	if ($this->ColActive) { $this->SetCol(0); }
/*-- END COLUMNS --*/


   	//RESET BLOCK BORDER TOP
   	if (!$this->ColActive) {
		for($bl=1;$bl<=$this->blklvl;$bl++) {
			$this->blk[$bl]['y0'] = $this->y;
			if (isset($this->blk[$bl]['x0'])) { $this->blk[$bl]['x0'] += $this->MarginCorrection; }
			else { $this->blk[$bl]['x0'] = $this->MarginCorrection; }
			// Added mPDF 3.0 Float DIV
			$this->blk[$bl]['marginCorrected'][$this->page] = true; 
		}
	}


	$this->table_rotate = $save_tr;	// *TABLES*
	$this->kwt = $save_kwt;

	$this->keep_block_together = $save_kt ;

	$this->cMarginL = $bak_cml;
	$this->cMarginR = $bak_cmr;
	$this->divwidth = $bak_dw;

	$this->lineheight = $bak_lh;
}


function PageNo() {
	//Get current page number
	return $this->page;
}

function AddSpotColorsFromFile($file) {
	$colors = @file($file) or die("Cannot load spot colors file - ".$file);
	foreach($colors AS $sc) {
		list($name, $c, $m, $y, $k) = preg_split("/\t/",$sc);
		$c = intval($c);
		$m = intval($m);
		$y = intval($y);
		$k = intval($k);
		$this->AddSpotColor($name, $c, $m, $y, $k);
	}
}

function AddSpotColor($name, $c, $m, $y, $k) {
	$name = strtoupper(trim($name));
	if(!isset($this->spotColors[$name])) {
		$i=count($this->spotColors)+1;
		$this->spotColors[$name]=array('i'=>$i,'c'=>$c,'m'=>$m,'y'=>$y,'k'=>$k);
		$this->spotColorIDs[$i]=$name;
	}
}

function SetColor($col, $type='') {
	$out = '';
	if ($col{0}==3 || $col{0}==5) {	// RGB / RGBa
		$out = sprintf('%.3F %.3F %.3F rg',ord($col{1})/255,ord($col{2})/255,ord($col{3})/255);
	}
	else if ($col{0}==1) {	// GRAYSCALE
		$out = sprintf('%.3F g',ord($col{1})/255);
	}
	else if ($col{0}==2) {	// SPOT COLOR
		$out = sprintf('/CS%d cs %.3F scn',ord($col{1}),ord($col{2})/100);
	}
	else if ($col{0}==4 || $col{0}==6) {	// CMYK / CMYKa
		$out = sprintf('%.3F %.3F %.3F %.3F k', ord($col{1})/100, ord($col{2})/100, ord($col{3})/100, ord($col{4})/100);
	}
	if ($type=='Draw') { $out = strtoupper($out); }	// e.g. rg => RG
	else if ($type=='CodeOnly') { $out = preg_replace('/\s(rg|g|k)/','',$out); }
	return $out; 
}


function SetDColor($col, $return=false) {
	$out = $this->SetColor($col, 'Draw');
	if ($return) { return $out; }
	if ($out=='') { return ''; }
	$this->DrawColor = $out;
	if($this->page>0 && ((isset($this->pageoutput[$this->page]['DrawColor']) && $this->pageoutput[$this->page]['DrawColor'] != $this->DrawColor) || !isset($this->pageoutput[$this->page]['DrawColor']) || $this->keep_block_together)) { $this->_out($this->DrawColor); }
	$this->pageoutput[$this->page]['DrawColor'] = $this->DrawColor;
}

function SetFColor($col, $return=false) {
	$out = $this->SetColor($col, 'Fill');
	if ($return) { return $out; }
	if ($out=='') { return ''; }
	$this->FillColor = $out;
	$this->ColorFlag = ($out != $this->TextColor);
	if($this->page>0 && ((isset($this->pageoutput[$this->page]['FillColor']) && $this->pageoutput[$this->page]['FillColor'] != $this->FillColor) || !isset($this->pageoutput[$this->page]['FillColor']) || $this->keep_block_together)) { $this->_out($this->FillColor); }
	$this->pageoutput[$this->page]['FillColor'] = $this->FillColor;
}

function SetTColor($col, $return=false) {
	$out = $this->SetColor($col, 'Text');
	if ($return) { return $out; }
	if ($out=='') { return ''; }
	$this->TextColor = $out;
	$this->ColorFlag = ($this->FillColor != $out);
} 


function SetDrawColor($r,$g=-1,$b=-1,$col4=-1, $return=false) {
	//Set color for all stroking operations
	$col = array();
	if(($r==0 and $g==0 and $b==0 && $col4 == -1) or $g==-1) { $col = $this->ConvertColor($r); }
	else if ($col4 == -1) { $col = $this->ConvertColor('rgb('.$r.','.$g.','.$b.')'); }
	else { $col = $this->ConvertColor('cmyk('.$r.','.$g.','.$b.','.$col4.')'); }
	$out = $this->SetDColor($col, $return);
	return $out;
}

function SetFillColor($r,$g=-1,$b=-1,$col4=-1, $return=false) {
	//Set color for all filling operations
	$col = array();
	if(($r==0 and $g==0 and $b==0 && $col4 == -1) or $g==-1) { $col = $this->ConvertColor($r); }
	else if ($col4 == -1) { $col = $this->ConvertColor('rgb('.$r.','.$g.','.$b.')'); }
	else { $col = $this->ConvertColor('cmyk('.$r.','.$g.','.$b.','.$col4.')'); }
	$out = $this->SetFColor($col, $return);
	return $out;
}

function SetTextColor($r,$g=-1,$b=-1,$col4=-1, $return=false) {
	//Set color for text
	$col = array();
	if(($r==0 and $g==0 and $b==0 && $col4 == -1) or $g==-1) { $col = $this->ConvertColor($r); }
	else if ($col4 == -1) { $col = $this->ConvertColor('rgb('.$r.','.$g.','.$b.')'); }
	else { $col = $this->ConvertColor('cmyk('.$r.','.$g.','.$b.','.$col4.')'); }
	$out = $this->SetTColor($col, $return);
	return $out;
}

function _getCharWidth(&$cw, $u, $isdef=true) {
	if ($u==0) { $w = false; }
	else { $w = (ord($cw[$u*2]) << 8) + ord($cw[$u*2+1]); }
	if ($w == 65535) { return 0; }
	else if ($w) { return $w; }
	else if ($isdef) { return false; }
	else { return 0; }
}

function _charDefined(&$cw, $u) {
	if ($u==0) { return false; }
	$w = (ord($cw[$u*2]) << 8) + ord($cw[$u*2+1]);
	if ($w) { return true; }
	else { return false; }
}

function GetCharWidthCore($c) {
	//Get width of a single character in the current Core font
	$c = (string)$c;
	$w = 0;
	// Soft Hyphens chr(173)
	if ($c == chr(173) && $this->FontFamily!='csymbol' && $this->FontFamily!='czapfdingbats') { 
		return 0;
	}
	else if ($this->S && isset($this->upperCase[ord($c)])) { 
		$charw = $this->CurrentFont['cw'][chr($this->upperCase[ord($c)])];
		if ($charw!==false) { 
			$charw = $charw*$this->smCapsScale * $this->smCapsStretch/100;
			$w+=$charw; 
		}
	}
	else if (isset($this->CurrentFont['cw'][$c])) { 
		$w += $this->CurrentFont['cw'][$c]; 
	} 
	else if (isset($this->CurrentFont['cw'][ord($c)])) { 
		$w += $this->CurrentFont['cw'][ord($c)]; 
	}
	$w *=  ($this->FontSize/ 1000);
	if ($this->minwSpacing || $this->fixedlSpacing) {
		if ($c==' ') $nb_spaces = 1;
		else $nb_spaces = 0;
		$w += $this->fixedlSpacing + ($nb_spaces * $this->minwSpacing);
	}
	return ($w);
}

function GetCharWidthNonCore($c, $addSubset=true) {
	//Get width of a single character in the current Non-Core font
	$c = (string)$c;
	$w = 0;
	$unicode = $this->UTF8StringToArray($c, $addSubset);
	$char = $unicode[0];
/*-- CJK-FONTS --*/
	if ($this->CurrentFont['type'] == 'Type0') {	// CJK Adobe fonts
			if ($char == 173) { return 0; }	// Soft Hyphens
			elseif (isset($this->CurrentFont['cw'][$char])) { $w+=$this->CurrentFont['cw'][$char]; } 
			elseif(isset($this->CurrentFont['MissingWidth'])) { $w += $this->CurrentFont['MissingWidth']; }
			else { $w += 500; }
	}
	else { 
/*-- END CJK-FONTS --*/
			if ($char == 173) { return 0; }	// Soft Hyphens
			else if ($this->S && isset($this->upperCase[$char])) {
				$charw = $this->_getCharWidth($this->CurrentFont['cw'],$this->upperCase[$char]);
				if ($charw!==false) { 
					$charw = $charw*$this->smCapsScale * $this->smCapsStretch/100;
					$w+=$charw; 
				}
				elseif(isset($this->CurrentFont['desc']['MissingWidth'])) { $w += $this->CurrentFont['desc']['MissingWidth']; }
				elseif(isset($this->CurrentFont['MissingWidth'])) { $w += $this->CurrentFont['MissingWidth']; }
				else { $w += 500; }
			}
			else {
				$charw = $this->_getCharWidth($this->CurrentFont['cw'],$char);
				if ($charw!==false) { $w+=$charw; }
				elseif(isset($this->CurrentFont['desc']['MissingWidth'])) { $w += $this->CurrentFont['desc']['MissingWidth']; }
				elseif(isset($this->CurrentFont['MissingWidth'])) { $w += $this->CurrentFont['MissingWidth']; }
				else { $w += 500; }
			}
	}	// *CJK-FONTS*
	$w *=  ($this->FontSize/ 1000);
	if ($this->minwSpacing || $this->fixedlSpacing) {
		if ($c==' ') $nb_spaces = 1;
		else $nb_spaces = 0;
		$w += $this->fixedlSpacing + ($nb_spaces * $this->minwSpacing);
	}
	return ($w);
}


function GetCharWidth($c, $addSubset=true) {
	if (!$this->usingCoreFont) {
		return $this->GetCharWidthNonCore($c, $addSubset);
	} 
	else {
		return $this->GetCharWidthCore($c);
	}
}

function GetStringWidth($s, $addSubset=true) {
			//Get width of a string in the current font
			$s = (string)$s;
			$cw = &$this->CurrentFont['cw'];
			$w = 0;
			$kerning = 0;
			$lastchar = 0;
			$nb_carac = 0;
			$nb_spaces = 0;
			// mPDF ITERATION
			if ($this->iterationCounter) $s = preg_replace('/{iteration ([a-zA-Z0-9_]+)}/', '\\1', $s);

			if (!$this->usingCoreFont) {
			      $s = str_replace("\xc2\xad",'',$s ); 
				$unicode = $this->UTF8StringToArray($s, $addSubset);
				if ($this->minwSpacing || $this->fixedlSpacing) {
					$nb_carac = count($unicode);  
					$nb_spaces = mb_substr_count($s,' ', $this->mb_enc);  
				}
/*-- CJK-FONTS --*/
				if ($this->CurrentFont['type'] == 'Type0') {	// CJK Adobe fonts
					foreach($unicode as $char) {
						if (isset($cw[$char])) { $w+=$cw[$char]; } 
						elseif(isset($this->CurrentFont['MissingWidth'])) { $w += $this->CurrentFont['MissingWidth']; }
						else { $w += 500; }
					}
				}
				else { 
/*-- END CJK-FONTS --*/
					foreach($unicode as $char) {
						if ($this->S && isset($this->upperCase[$char])) {
							$charw = $this->_getCharWidth($cw,$this->upperCase[$char]);
							if ($charw!==false) { 
								$charw = $charw*$this->smCapsScale * $this->smCapsStretch/100;
								$w+=$charw; 
							}
							elseif(isset($this->CurrentFont['desc']['MissingWidth'])) { $w += $this->CurrentFont['desc']['MissingWidth']; }
							elseif(isset($this->CurrentFont['MissingWidth'])) { $w += $this->CurrentFont['MissingWidth']; }
							else { $w += 500; }
						}
						else {
							$charw = $this->_getCharWidth($cw,$char);
							if ($charw!==false) { $w+=$charw; }
							elseif(isset($this->CurrentFont['desc']['MissingWidth'])) { $w += $this->CurrentFont['desc']['MissingWidth']; }
							elseif(isset($this->CurrentFont['MissingWidth'])) { $w += $this->CurrentFont['MissingWidth']; }
							else { $w += 500; }
							if ($this->kerning && $this->useKerning && $lastchar) {
								if (isset($this->CurrentFont['kerninfo'][$lastchar][$char])) { 
									$kerning += $this->CurrentFont['kerninfo'][$lastchar][$char]; 
								}
							}
							$lastchar = $char;
						}
					}
				}	// *CJK-FONTS*

			} 
			else {
				if ($this->FontFamily!='csymbol' && $this->FontFamily!='czapfdingbats') { 
			      	$s = str_replace(chr(173),'',$s ); 
				}
				$nb_carac = $l = strlen($s);
				if ($this->minwSpacing || $this->fixedlSpacing) {
					$nb_spaces = substr_count($s,' ');  
				}
				for($i=0; $i<$l; $i++) {
					if ($this->S && isset($this->upperCase[ord($s[$i])])) { 
						$charw = $cw[chr($this->upperCase[ord($s[$i])])];
						if ($charw!==false) { 
							$charw = $charw*$this->smCapsScale * $this->smCapsStretch/100;
							$w+=$charw; 
						}
					}
					else if (isset($cw[$s[$i]])) { 
						$w += $cw[$s[$i]]; 
					} 
					else if (isset($cw[ord($s[$i])])) { 
						$w += $cw[ord($s[$i])]; 
					}
					if ($this->kerning && $this->useKerning && $i>0) {
						if (isset($this->CurrentFont['kerninfo'][$s[($i-1)]][$s[$i]])) { 
							$kerning += $this->CurrentFont['kerninfo'][$s[($i-1)]][$s[$i]]; 
						}
					}
				}
			}
			unset($cw);
			if ($this->kerning && $this->useKerning) { $w += $kerning; }
			$w *=  ($this->FontSize/ 1000);
			$w += (($nb_carac + $nb_spaces) * $this->fixedlSpacing) + ($nb_spaces * $this->minwSpacing);
			return ($w);
}

function SetLineWidth($width) {
	//Set line width
	$this->LineWidth=$width;
	$lwout = (sprintf('%.3F w',$width*_MPDFK));
	if($this->page>0 && ((isset($this->pageoutput[$this->page]['LineWidth']) && $this->pageoutput[$this->page]['LineWidth'] != $lwout) || !isset($this->pageoutput[$this->page]['LineWidth']) || $this->keep_block_together)) {
		 $this->_out($lwout); 
	}
	$this->pageoutput[$this->page]['LineWidth'] = $lwout;
}

function Line($x1,$y1,$x2,$y2) {
	//Draw a line
	$this->_out(sprintf('%.3F %.3F m %.3F %.3F l S',$x1*_MPDFK,($this->h-$y1)*_MPDFK,$x2*_MPDFK,($this->h-$y2)*_MPDFK));
}

function Arrow($x1,$y1,$x2,$y2,$headsize=3,$fill='B',$angle=25) {
  //F == fill //S == stroke //B == stroke and fill 
  // angle = splay of arrowhead - 1 - 89 degrees
  if($fill=='F')	$fill='f';
  elseif($fill=='FD' or $fill=='DF' or $fill=='B') $fill='B';
  else $fill='S';
  $a = atan2(($y2-$y1),($x2-$x1));
  $b = $a + deg2rad($angle);
  $c = $a - deg2rad($angle);
  $x3 = $x2 - ($headsize* cos($b));
  $y3 = $this->h-($y2 - ($headsize* sin($b)));
  $x4 = $x2 - ($headsize* cos($c));
  $y4 = $this->h-($y2 - ($headsize* sin($c)));

  $x5 = $x3-($x3-$x4)/2;	// mid point of base of arrowhead - to join arrow line to
  $y5 = $y3-($y3-$y4)/2;

  $s = '';
  $s.=sprintf('%.3F %.3F m %.3F %.3F l S',$x1*_MPDFK,($this->h-$y1)*_MPDFK,$x5*_MPDFK,$y5*_MPDFK);
  $this->_out($s);

  $s = '';
  $s.=sprintf('%.3F %.3F m %.3F %.3F l %.3F %.3F l %.3F %.3F l %.3F %.3F l ',$x5*_MPDFK,$y5*_MPDFK,$x3*_MPDFK,$y3*_MPDFK,$x2*_MPDFK,($this->h-$y2)*_MPDFK,$x4*_MPDFK,$y4*_MPDFK,$x5*_MPDFK,$y5*_MPDFK);
  $s.=$fill;
  $this->_out($s);
}


function Rect($x,$y,$w,$h,$style='') {
	//Draw a rectangle
	if($style=='F')	$op='f';
	elseif($style=='FD' or $style=='DF') $op='B';
	else $op='S';
	$this->_out(sprintf('%.3F %.3F %.3F %.3F re %s',$x*_MPDFK,($this->h-$y)*_MPDFK,$w*_MPDFK,-$h*_MPDFK,$op));
}

function AddFont($family,$style='') {
	if(empty($family)) { return; }
	$family = strtolower($family);
	$style=strtoupper($style);
	$style=str_replace('U','',$style);
	if($style=='IB') $style='BI';
	$fontkey = $family.$style;
	// check if the font has been already added
	if(isset($this->fonts[$fontkey])) {
		return;
	}

/*-- CJK-FONTS --*/
	if (in_array($family,$this->available_CJK_fonts)) { 
		if (empty($this->Big5_widths)) { require(_MPDF_PATH . 'includes/CJKdata.php'); }
		$this->AddCJKFont($family);	// don't need to add style
		return; 
	}
/*-- END CJK-FONTS --*/

	if ($this->usingCoreFont) { die("mPDF Error - problem with Font management"); }

	$stylekey = $style;
	if (!$style) { $stylekey = 'R'; }

	if (!isset($this->fontdata[$family][$stylekey]) || !$this->fontdata[$family][$stylekey]) {
		die('mPDF Error - Font is not supported - '.$family.' '.$style);
	}

	$name = '';
	$originalsize = 0;
	$sip = false;
	$smp = false;
	$unAGlyphs = false;	// mPDF 5.4.05
	$haskerninfo = false;
	$BMPselected = false;
	@include(_MPDF_TTFONTDATAPATH.$fontkey.'.mtx.php');

	$ttffile = '';
	if (defined('_MPDF_SYSTEM_TTFONTS')) {
		$ttffile = _MPDF_SYSTEM_TTFONTS.$this->fontdata[$family][$stylekey];
		if (!file_exists($ttffile)) { $ttffile = ''; }
	}
	if (!$ttffile) {
		$ttffile = _MPDF_TTFONTPATH.$this->fontdata[$family][$stylekey];
		if (!file_exists($ttffile)) { die("mPDF Error - cannot find TTF TrueType font file - ".$ttffile); }
	}
	$ttfstat = stat($ttffile);

	if (isset($this->fontdata[$family]['TTCfontID'][$stylekey])) { $TTCfontID = $this->fontdata[$family]['TTCfontID'][$stylekey]; }
	else { $TTCfontID = 0; }


	$BMPonly = false;
	if (in_array($family,$this->BMPonly)) { $BMPonly = true; }
	$regenerate = false;
	if ($BMPonly && !$BMPselected) { $regenerate = true; }
	else if (!$BMPonly && $BMPselected) { $regenerate = true; }
	if ($this->useKerning && !$haskerninfo) { $regenerate = true; }
	// mPDF 5.4.05
	if (isset($this->fontdata[$family]['unAGlyphs']) && $this->fontdata[$family]['unAGlyphs'] && !$unAGlyphs) { 
		$regenerate = true; 
		$unAGlyphs = true;
	}
	else if ((!isset($this->fontdata[$family]['unAGlyphs']) || !$this->fontdata[$family]['unAGlyphs']) && $unAGlyphs) { 
		$regenerate = true; 
		$unAGlyphs = false;
	}
	if (!isset($name) || $originalsize != $ttfstat['size'] || $regenerate) {
		if (!class_exists('TTFontFile', false)) { include(_MPDF_PATH .'classes/ttfontsuni.php'); }
		$ttf = new TTFontFile();
		$ttf->getMetrics($ttffile, $TTCfontID, $this->debugfonts, $BMPonly, $this->useKerning, $unAGlyphs);	// mPDF 5.4.05
		$cw = $ttf->charWidths;
		$kerninfo = $ttf->kerninfo;
		$haskerninfo = true;
		$name = preg_replace('/[ ()]/','',$ttf->fullName);
		$sip = $ttf->sipset;
		$smp = $ttf->smpset;

		$desc= array('Ascent'=>round($ttf->ascent),
		'Descent'=>round($ttf->descent),
		'CapHeight'=>round($ttf->capHeight),
		'Flags'=>$ttf->flags,
		'FontBBox'=>'['.round($ttf->bbox[0])." ".round($ttf->bbox[1])." ".round($ttf->bbox[2])." ".round($ttf->bbox[3]).']',
		'ItalicAngle'=>$ttf->italicAngle,
		'StemV'=>round($ttf->stemV),
		'MissingWidth'=>round($ttf->defaultWidth));
		$panose = '';
		// mPDF 5.5.19
		if (count($ttf->panose)) {
			$panoseArray = array_merge(array($ttf->sFamilyClass, $ttf->sFamilySubClass), $ttf->panose);
			foreach($panoseArray as $value)
				$panose .= ' '.dechex($value);
		}
		$up = round($ttf->underlinePosition);
		$ut = round($ttf->underlineThickness);
		$originalsize = $ttfstat['size']+0;
		$type = 'TTF';
		//Generate metrics .php file
		$s='<?php'."\n";
		$s.='$name=\''.$name."';\n";
		$s.='$type=\''.$type."';\n";
		$s.='$desc='.var_export($desc,true).";\n";
		$s.='$up='.$up.";\n";
		$s.='$ut='.$ut.";\n";
		$s.='$ttffile=\''.$ttffile."';\n";
		$s.='$TTCfontID=\''.$TTCfontID."';\n";
		$s.='$originalsize='.$originalsize.";\n";
		if ($sip) $s.='$sip=true;'."\n";
		else $s.='$sip=false;'."\n";
		if ($smp) $s.='$smp=true;'."\n";
		else $s.='$smp=false;'."\n";
		if ($BMPonly) $s.='$BMPselected=true;'."\n";
		else $s.='$BMPselected=false;'."\n";
		$s.='$fontkey=\''.$fontkey."';\n";
		$s.='$panose=\''.$panose."';\n";
		if ($this->useKerning) { 
			$s.='$kerninfo='.var_export($kerninfo,true).";\n"; 
			$s.='$haskerninfo=true;'."\n";
		}
		else $s.='$haskerninfo=false;'."\n";
		// mPDF 5.4.05
		if ($this->fontdata[$family]['unAGlyphs']) { 
			$s.='$unAGlyphs=true;'."\n";
		}
		else $s.='$unAGlyphs=false;'."\n";
		$s.="?>";
		if (is_writable(dirname(_MPDF_TTFONTDATAPATH.'x'))) {
			$fh = fopen(_MPDF_TTFONTDATAPATH.$fontkey.'.mtx.php',"w");
			fwrite($fh,$s,strlen($s));
			fclose($fh);
			$fh = fopen(_MPDF_TTFONTDATAPATH.$fontkey.'.cw.dat',"wb");
			fwrite($fh,$cw,strlen($cw));
			fclose($fh);
			@unlink(_MPDF_TTFONTDATAPATH.$fontkey.'.cgm');
			@unlink(_MPDF_TTFONTDATAPATH.$fontkey.'.z');
			@unlink(_MPDF_TTFONTDATAPATH.$fontkey.'.cw127.php');
			@unlink(_MPDF_TTFONTDATAPATH.$fontkey.'.cw');
		}
		else if ($this->debugfonts) { $this->Error('Cannot write to the font caching directory - '._MPDF_TTFONTDATAPATH); }
		unset($ttf);
	}
	else {
		$cw = @file_get_contents(_MPDF_TTFONTDATAPATH.$fontkey.'.cw.dat'); 
	}

	if (isset($this->fontdata[$family]['indic']) && $this->fontdata[$family]['indic']) { $indic = true; }
	else { $indic = false; }
	if (isset($this->fontdata[$family]['sip-ext']) && $this->fontdata[$family]['sip-ext']) { $sipext = $this->fontdata[$family]['sip-ext']; }
	else { $sipext = ''; }


	$i = count($this->fonts)+$this->extraFontSubsets+1;
	if ($sip || $smp) {
		$this->fonts[$fontkey] = array('i'=>$i, 'type'=>$type, 'name'=>$name, 'desc'=>$desc, 'panose'=>$panose, 'up'=>$up, 'ut'=>$ut, 'cw'=>$cw, 'ttffile'=>$ttffile, 'fontkey'=>$fontkey, 'subsets'=>array(0=>range(0,127)), 'subsetfontids'=>array($i), 'used'=>false, 'indic'=>$indic, 'sip'=>$sip, 'sipext'=>$sipext, 'smp'=>$smp, 'TTCfontID' => $TTCfontID, 'unAGlyphs' => false);	// mPDF 5.4.05
	}
	else  {
		$ss = array();
		for ($s=32; $s<128; $s++) { $ss[$s] = $s; }
		$this->fonts[$fontkey] = array('i'=>$i, 'type'=>$type, 'name'=>$name, 'desc'=>$desc, 'panose'=>$panose, 'up'=>$up, 'ut'=>$ut, 'cw'=>$cw, 'ttffile'=>$ttffile, 'fontkey'=>$fontkey, 'subset'=>$ss, 'used'=>false, 'indic'=>$indic, 'sip'=>$sip, 'sipext'=>$sipext, 'smp'=>$smp, 'TTCfontID' => $TTCfontID, 'unAGlyphs' => $unAGlyphs);	// mPDF 5.4.05
	}
	if ($this->useKerning && $haskerninfo) { $this->fonts[$fontkey]['kerninfo'] = $kerninfo; }
	$this->FontFiles[$fontkey]=array('length1'=>$originalsize, 'type'=>"TTF", 'ttffile'=>$ttffile, 'sip'=>$sip, 'smp'=>$smp);
	unset($cw);
}



function SetFont($family,$style='',$size=0, $write=true, $forcewrite=false) {
	$family=strtolower($family);
	if (!$this->onlyCoreFonts) {
		if ($family == 'sans' || $family == 'sans-serif') { $family = $this->sans_fonts[0]; }
		if ($family == 'serif') { $family = $this->serif_fonts[0]; }
		if ($family == 'mono' || $family == 'monospace') { $family = $this->mono_fonts[0]; }
	}
	if (isset($this->fonttrans[$family]) && $this->fonttrans[$family]) { $family = $this->fonttrans[$family]; }
	if($family=='') { 
		if ($this->FontFamily) { $family=$this->FontFamily; }
		else if ($this->default_font) { $family=$this->default_font; }
		else { $this->Error("No font or default font set!"); }
	}
	$this->ReqFontStyle = $style;	// required or requested style - used later for artificial bold/italic

	if (($family == 'csymbol') || ($family == 'czapfdingbats')  || ($family == 'ctimes')  || ($family == 'ccourier') || ($family == 'chelvetica')) { 
		if ($this->PDFA || $this->PDFX) {
		   if ($family == 'csymbol' || $family == 'czapfdingbats') { 
			$this->Error("Symbol and Zapfdingbats cannot be embedded in mPDF (required for PDFA1-b or PDFX/1-a).");
		   }
		   if ($family == 'ctimes'  || $family == 'ccourier' || $family == 'chelvetica') { 
			if (($this->PDFA && !$this->PDFAauto) || ($this->PDFX && !$this->PDFXauto)) { $this->PDFAXwarnings[] = "Core Adobe font ".ucfirst($family)." cannot be embedded in mPDF, which is required for PDFA1-b or PDFX/1-a. (Embedded font will be substituted.)"; }
			if ($family == 'chelvetica') { $family = 'sans'; }
			if ($family == 'ctimes') { $family = 'serif'; }
			if ($family == 'ccourier') { $family = 'mono'; }
		   }
		   $this->usingCoreFont = false;
		}
		else { $this->usingCoreFont = true; }
		if($family=='csymbol' || $family=='czapfdingbats') { $style=''; }
	}
	else {  $this->usingCoreFont = false; }

	$this->U=false;
	$this->S=false;
	if ($style) {
		$style=strtoupper($style);
		if(strpos($style,'U')!==false) {
			$this->U=true;
			$style=str_replace('U','',$style);
		}
		if(strpos($style,'S')!==false) {
			$this->S=true;
			// Small Caps
			if (empty($this->upperCase)) { @include(_MPDF_PATH.'includes/upperCase.php'); } 
			$style=str_replace('S','',$style);
		}
		if ($style=='IB') $style='BI';
	}
	if ($size==0) $size=$this->FontSizePt;

	$fontkey=$family.$style;

	$stylekey = $style;
	if (!$stylekey) { $stylekey = "R"; }

	if (!$this->onlyCoreFonts && !$this->usingCoreFont) {
		if(!isset($this->fonts[$fontkey]) || count($this->default_available_fonts) != count($this->available_unifonts) ) { // not already added
/*-- CJK-FONTS --*/
		  // CJK fonts
		  if (in_array($fontkey,$this->available_CJK_fonts)) {
			if(!isset($this->fonts[$fontkey])) {	// already added
				if (empty($this->Big5_widths)) { require(_MPDF_PATH . 'includes/CJKdata.php'); }
				$this->AddCJKFont($family);	// don't need to add style
			}
		  }
		  // Test to see if requested font/style is available - or substitute
		  else
/*-- END CJK-FONTS --*/
		  if (!in_array($fontkey,$this->available_unifonts)) {
			// If font[nostyle] exists - set it
			if (in_array($family,$this->available_unifonts)) {
				$style = '';
			}

			// Else if only one font available - set it (assumes if only one font available it will not have a style)
			else if (count($this->available_unifonts) == 1) {
				$family = $this->available_unifonts[0];
				$style = '';
			}

			else {
				$found = 0;
				// else substitute font of similar type
				if (in_array($family,$this->sans_fonts)) { 
					$i = array_intersect($this->sans_fonts,$this->available_unifonts);
					if (count($i)) {
						$i = array_values($i);
						// with requested style if possible
						if (!in_array(($i[0].$style),$this->available_unifonts)) {
							$style = '';
						}
						$family = $i[0]; 
						$found = 1;
					}
				}
				else if (in_array($family,$this->serif_fonts)) { 
					$i = array_intersect($this->serif_fonts,$this->available_unifonts);
					if (count($i)) {
						$i = array_values($i);
						// with requested style if possible
						if (!in_array(($i[0].$style),$this->available_unifonts)) {
							$style = '';
						}
						$family = $i[0]; 
						$found = 1;
					}
				}
				else if (in_array($family,$this->mono_fonts)) {
					$i = array_intersect($this->mono_fonts,$this->available_unifonts);
					if (count($i)) {
						$i = array_values($i);
						// with requested style if possible
						if (!in_array(($i[0].$style),$this->available_unifonts)) {
							$style = '';
						}
						$family = $i[0]; 
						$found = 1;
					}
				}

				if (!$found) {
					// set first available font
					$fs = $this->available_unifonts[0];
					preg_match('/^([a-z_0-9\-]+)([BI]{0,2})$/',$fs,$fas);	// Allow "-"
					// with requested style if possible
					$ws = $fas[1].$style;
					if (in_array($ws,$this->available_unifonts)) {
						$family = $fas[1]; // leave $style as is
					}
					else if (in_array($fas[1],$this->available_unifonts)) {
					// or without style
						$family = $fas[1];
						$style = '';
					}
					else {
					// or with the style specified 
						$family = $fas[1];
						$style = $fas[2];
					}
				}
			}
			$fontkey = $family.$style; 
		  }
		}
		// try to add font (if not already added)
		$this->AddFont($family, $style);

		//Test if font is already selected
		if($this->FontFamily == $family && $this->FontFamily == $this->currentfontfamily && $this->FontStyle == $style && $this->FontStyle == $this->currentfontstyle && $this->FontSizePt == $size && $this->FontSizePt == $this->currentfontsize && !$forcewrite) {
			return $family;
		}

		$fontkey = $family.$style; 

		//Select it
		$this->FontFamily = $family;
		$this->FontStyle = $style;
		$this->FontSizePt = $size;
		$this->FontSize = $size / _MPDFK;
		$this->CurrentFont = &$this->fonts[$fontkey];
		if ($write) { 
			$fontout = (sprintf('BT /F%d %.3F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
			if($this->page>0 && ((isset($this->pageoutput[$this->page]['Font']) && $this->pageoutput[$this->page]['Font'] != $fontout) || !isset($this->pageoutput[$this->page]['Font']) || $this->keep_block_together)) { $this->_out($fontout); }
			$this->pageoutput[$this->page]['Font'] = $fontout;
		}



		// Added - currentfont (lowercase) used in HTML2PDF
		$this->currentfontfamily=$family;
		$this->currentfontsize=$size;
		$this->currentfontstyle=$style.($this->U ? 'U' : '').($this->S ? 'S' : '');
		$this->setMBencoding('UTF-8');
	}

	else { 	// if using core fonts


		if ($this->PDFA || $this->PDFX) {
			$this->Error('Core Adobe fonts cannot be embedded in mPDF (required for PDFA1-b or PDFX/1-a) - cannot use option to use core fonts.');
		}
		$this->setMBencoding('windows-1252');

		//Test if font is already selected
		if(($this->FontFamily == $family) AND ($this->FontStyle == $style) AND ($this->FontSizePt == $size) && !$forcewrite) {
			return $family;
		}

		if (!isset($this->CoreFonts[$fontkey])) {
			if (in_array($family,$this->serif_fonts)) { $family = 'ctimes'; }
			else if (in_array($family,$this->mono_fonts)) { $family = 'ccourier'; }
			else { $family = 'chelvetica'; }
			$this->usingCoreFont = true;
			$fontkey = $family.$style; 
		}

		if(!isset($this->fonts[$fontkey])) 	{
			// STANDARD CORE FONTS
			if (isset($this->CoreFonts[$fontkey])) {
				//Load metric file
				$file=$family;
				if($family=='ctimes' || $family=='chelvetica' || $family=='ccourier') { $file.=strtolower($style); }
				$file.='.php';
				include(_MPDF_PATH.'font/'.$file);
				if(!isset($cw)) { $this->Error('Could not include font metric file'); }
				$i=count($this->fonts)+$this->extraFontSubsets+1;
				$this->fonts[$fontkey]=array('i'=>$i,'type'=>'core','name'=>$this->CoreFonts[$fontkey],'desc'=>$desc,'up'=>$up,'ut'=>$ut,'cw'=>$cw);
				if ($this->useKerning) { $this->fonts[$fontkey]['kerninfo'] = $kerninfo; }
			}
			else {
				die('mPDF error - Font not defined');
			}
		}
		//Test if font is already selected
		if(($this->FontFamily == $family) AND ($this->FontStyle == $style) AND ($this->FontSizePt == $size) && !$forcewrite) {
			return $family;
		}
		//Select it
		$this->FontFamily=$family;
		$this->FontStyle=$style;
		$this->FontSizePt=$size;
		$this->FontSize=$size/_MPDFK;
		$this->CurrentFont=&$this->fonts[$fontkey];
		if ($write) { 
			$fontout = (sprintf('BT /F%d %.3F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
			if($this->page>0 && ((isset($this->pageoutput[$this->page]['Font']) && $this->pageoutput[$this->page]['Font'] != $fontout) || !isset($this->pageoutput[$this->page]['Font']) || $this->keep_block_together)) { $this->_out($fontout); }
			$this->pageoutput[$this->page]['Font'] = $fontout;
		}
		// Added - currentfont (lowercase) used in HTML2PDF
		$this->currentfontfamily=$family;
		$this->currentfontsize=$size;
		$this->currentfontstyle=$style.($this->U ? 'U' : '').($this->S ? 'S' : '');

	}

	return $family;
}

function SetFontSize($size,$write=true) {
	//Set font size in points
	if($this->FontSizePt==$size) return;
	$this->FontSizePt=$size;
	$this->FontSize=$size/_MPDFK;
	$this->currentfontsize=$size;
		if ($write) { 
			$fontout = (sprintf('BT /F%d %.3F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
			// Edited mPDF 3.0
			if($this->page>0 && ((isset($this->pageoutput[$this->page]['Font']) && $this->pageoutput[$this->page]['Font'] != $fontout) || !isset($this->pageoutput[$this->page]['Font']) || $this->keep_block_together)) { $this->_out($fontout); }
			$this->pageoutput[$this->page]['Font'] = $fontout;
		}
}

function AddLink() {
	//Create a new internal link
	$n=count($this->links)+1;
	$this->links[$n]=array(0,0);
	return $n;
}

function SetLink($link,$y=0,$page=-1) {
	//Set destination of internal link
	if($y==-1) $y=$this->y;
	if($page==-1)	$page=$this->page;
	$this->links[$link]=array($page,$y);
}

function Link($x,$y,$w,$h,$link) {
	$l = array($x*_MPDFK,$this->hPt-$y*_MPDFK,$w*_MPDFK,$h*_MPDFK,$link);
	if ($this->keep_block_together) {	// Save to array - don't write yet
		$this->ktLinks[$this->page][]= $l;
		return;
	}
	else if ($this->table_rotate) {	// *TABLES*
		$this->tbrot_Links[$this->page][]= $l;	// *TABLES*
		return;	// *TABLES*
	}	// *TABLES*
	else if ($this->kwt) {
		$this->kwt_Links[$this->page][]= $l;
		return;
	}

	if ($this->writingHTMLheader || $this->writingHTMLfooter) {
		$this->HTMLheaderPageLinks[]= $l;
		return;
	}
	//Put a link on the page
	$this->PageLinks[$this->page][]= $l;
	// Save cross-reference to Column buffer
	$ref = count($this->PageLinks[$this->page])-1;	// *COLUMNS*
	$this->columnLinks[$this->CurrCol][INTVAL($this->x)][INTVAL($this->y)] = $ref;	// *COLUMNS*

}

function Text($x,$y,$txt) {
	// Output a string
	// Called (internally) by Watermark and _tableWrite [rotated cells]
	// Expects input to be mb_encoded if necessary and RTL reversed

	// ARTIFICIAL BOLD AND ITALIC
	$s = 'q ';
	if ($this->falseBoldWeight && strpos($this->ReqFontStyle,"B") !== false && strpos($this->FontStyle,"B") === false) {
		$s  .= '2 Tr 1 J 1 j ';
		$s .= sprintf('%.3F w ',($this->FontSize/130)*_MPDFK*$this->falseBoldWeight);
		$tc = strtoupper($this->TextColor); // change 0 0 0 rg to 0 0 0 RG
		if($this->FillColor!=$tc) { $s .= $tc.' '; }		// stroke (outline) = same colour as text(fill)
	}
	if (strpos($this->ReqFontStyle,"I") !== false && strpos($this->FontStyle,"I") === false) {
		$aix = '1 0 0.261799 1 %.3F %.3F Tm'; 
	}
	else { $aix = '%.3F %.3F Td'; }

	if($this->ColorFlag) $s.=$this->TextColor.' ';

	$this->CurrentFont['used']= true;
	if ($this->CurrentFont['type']=='TTF' && ($this->CurrentFont['sip'] || $this->CurrentFont['smp'])) {
	      $txt2 = str_replace(chr(194).chr(160),chr(32),$txt);
		$txt2 = $this->UTF8toSubset($txt2);
		$s.=sprintf('BT '.$aix.' %s Tj ET ',$x*_MPDFK,($this->h-$y)*_MPDFK,$txt2);
	}
	else if (!$this->usingCoreFont) {
	      $txt2 = str_replace(chr(194).chr(160),chr(32),$txt); 
		$this->UTF8StringToArray($txt2);	// this is just to add chars to subset list
		if ($this->kerning && $this->useKerning) { $s .= $this->_kern($txt2, '', $aix, $x, $y); }
		else {
			//Convert string to UTF-16BE without BOM
			$txt2= $this->UTF8ToUTF16BE($txt2, false);
			$s.=sprintf('BT '.$aix.' (%s) Tj ET ',$x*_MPDFK,($this->h-$y)*_MPDFK,$this->_escape($txt2));
		}
	}
	else {
	      $txt2 = str_replace(chr(160),chr(32),$txt);
		if ($this->kerning && $this->useKerning) { $s .= $this->_kern($txt2, '', $aix, $x, $y); }
		else {
			$s.=sprintf('BT '.$aix.' (%s) Tj ET ',$x*_MPDFK,($this->h-$y)*_MPDFK,$this->_escape($txt2));
		}
	}
	if($this->U && $txt!='') {
		$c = strtoupper($this->TextColor); // change 0 0 0 rg to 0 0 0 RG
		if($this->FillColor!=$c) { $s.= ' '.$c.' '; }
		if (isset($this->CurrentFont['up'])) { $up=$this->CurrentFont['up']; }
		else { $up = -100; }
		$adjusty = (-$up/1000* $this->FontSize);
 		if (isset($this->CurrentFont['ut'])) { $ut=$this->CurrentFont['ut']/1000* $this->FontSize; }
		else { $ut = 60/1000* $this->FontSize; }
		$olw = $this->LineWidth;
		$s.=' '.(sprintf(' %.3F w',$ut*_MPDFK));
		$s.=' '.$this->_dounderline($x,$y + $adjusty,$txt);
		$s.=' '.(sprintf(' %.3F w',$olw*_MPDFK));
		if($this->FillColor!=$c) { $s.= ' '.$this->FillColor.' '; }
	}
   	// STRIKETHROUGH
	if($this->strike && $txt!='') {
		$c = strtoupper($this->TextColor); // change 0 0 0 rg to 0 0 0 RG
		if($this->FillColor!=$c) { $s.= ' '.$c.' '; }
    		//Superscript and Subscript Y coordinate adjustment (now for striked-through texts)
		if (isset($this->CurrentFont['desc']['CapHeight'])) { $ch=$this->CurrentFont['desc']['CapHeight']; }
		else { $ch = 700; }
		$adjusty = (-$ch/1000* $this->FontSize) * 0.35;
 		if (isset($this->CurrentFont['ut'])) { $ut=$this->CurrentFont['ut']/1000* $this->FontSize; }
		else { $ut = 60/1000* $this->FontSize; }
		$olw = $this->LineWidth;
		$s.=' '.(sprintf(' %.3F w',$ut*_MPDFK));
		$s.=' '.$this->_dounderline($x,$y+$adjusty,$txt);
		$s.=' '.(sprintf(' %.3F w',$olw*_MPDFK));
		if($this->FillColor!=$c) { $s.= ' '.$this->FillColor.' '; }
	}
	$s .= 'Q';
	$this->_out($s);
}

/*-- DIRECTW --*/
function WriteText($x,$y,$txt) {
	// Output a string using Text() but does encoding and text reversing of RTL
	$txt = $this->purify_utf8_text($txt);
	if ($this->text_input_as_HTML) {
		$txt = $this->all_entities_to_utf8($txt);
	}
	if ($this->usingCoreFont) { $txt = mb_convert_encoding($txt,$this->mb_enc,'UTF-8'); }
	// DIRECTIONALITY
	if (preg_match("/([".$this->pregRTLchars."])/u", $txt)) { $this->biDirectional = true; }	// *RTL*
	$this->magic_reverse_dir($txt, true, $this->directionality);	// *RTL*
	// Font-specific ligature substitution for Indic fonts
	if (isset($this->CurrentFont['indic']) && $this->CurrentFont['indic']) $this->ConvertIndic($txt);	// *INDIC*
	$this->Text($x,$y,$txt);
}

function WriteCell($w,$h=0,$txt='',$border=0,$ln=0,$align='',$fill=0,$link='', $currentx=0) {
	//Output a cell using Cell() but does encoding and text reversing of RTL
	$txt = $this->purify_utf8_text($txt);
	if ($this->text_input_as_HTML) {
		$txt = $this->all_entities_to_utf8($txt);
	}
	if ($this->usingCoreFont) { $txt = mb_convert_encoding($txt,$this->mb_enc,'UTF-8'); }
	// DIRECTIONALITY
	if (preg_match("/([".$this->pregRTLchars."])/u", $txt)) { $this->biDirectional = true; }	// *RTL*
	$this->magic_reverse_dir($txt, true, $this->directionality);	// *RTL*
	// Font-specific ligature substitution for Indic fonts
	if (isset($this->CurrentFont['indic']) && $this->CurrentFont['indic']) $this->ConvertIndic($txt);	// *INDIC*
	$this->Cell($w,$h,$txt,$border,$ln,$align,$fill,$link, $currentx);
}
/*-- END DIRECTW --*/


function ResetSpacing() {
	if ($this->ws != 0) { $this->_out('BT 0 Tw ET'); }
	$this->ws=0;
	if ($this->charspacing != 0) { $this->_out('BT 0 Tc ET'); }
	$this->charspacing=0;
}


function SetSpacing($cs,$ws) {
	if (intval($cs*1000)==0) { $cs = 0; }
	if ($cs) { $this->_out(sprintf('BT %.3F Tc ET',$cs)); }
	else if ($this->charspacing != 0) { $this->_out('BT 0 Tc ET'); }
	$this->charspacing=$cs;
	if (intval($ws*1000)==0) { $ws = 0; }
	if ($ws) { $this->_out(sprintf('BT %.3F Tw ET',$ws)); }
	else if ($this->ws != 0) { $this->_out('BT 0 Tw ET'); }
	$this->ws=$ws;
}

// WORD SPACING
function GetJspacing($nc,$ns,$w,$inclCursive) {
	$ws = 0; 
	$charspacing = 0;
	$ww = $this->jSWord;
	$ncx = $nc-1;
	if ($nc == 0) { return array(0,0); }
	else if ($nc==1) { $charspacing = $w; }
	// Only word spacing allowed / possible
	else if ($this->fixedlSpacing !== false || $inclCursive) {
		if ($ns) { $ws = $w / $ns; } 
	}
	else if (!$ns) {
		$charspacing = $w / ($ncx );
		if (($this->jSmaxChar > 0) && ($charspacing > $this->jSmaxChar)) { 
			$charspacing = $this->jSmaxChar;
		}
	}
	else if ($ns == ($ncx )) {
		$charspacing = $w / $ns;
	}
	else {
		if ($this->usingCoreFont) {
			$cs = ($w * (1 - $this->jSWord)) / ($ncx );
			if (($this->jSmaxChar > 0) && ($cs > $this->jSmaxChar)) {
				$cs = $this->jSmaxChar;
				$ww = 1 - (($cs * ($ncx ))/$w);
			}
			$charspacing = $cs; 
			$ws = ($w * ($ww) ) / $ns;
		}
		else {
			$cs = ($w * (1 - $this->jSWord)) / ($ncx -$ns);
			if (($this->jSmaxChar > 0) && ($cs > $this->jSmaxChar)) {
				$cs = $this->jSmaxChar;
				$ww = 1 - (($cs * ($ncx -$ns))/$w);
			}
			$charspacing = $cs; 
			$ws = (($w * ($ww) ) / $ns) - $charspacing;
		}
	}
	return array($charspacing,$ws); 
}

function Cell($w,$h=0,$txt='',$border=0,$ln=0,$align='',$fill=0,$link='', $currentx=0, $lcpaddingL=0, $lcpaddingR=0, $valign='M', $spanfill=0, $abovefont=0, $belowfont=0, $exactWidth=false) {
	//Output a cell
	// Expects input to be mb_encoded if necessary and RTL reversed
	// NON_BREAKING SPACE
	if ($this->usingCoreFont) {
	      $txt = str_replace(chr(160),chr(32),$txt);
	}
	else {
	      $txt = str_replace(chr(194).chr(160),chr(32),$txt);
	}

	$oldcolumn = $this->CurrCol;
	// Automatic page break
	// Allows PAGE-BREAK-AFTER = avoid to work

	if (!$this->tableLevel && (($this->y+$this->divheight>$this->PageBreakTrigger) || ($this->y+$h>$this->PageBreakTrigger) || 
		($this->y+($h*2)>$this->PageBreakTrigger && $this->blk[$this->blklvl]['page_break_after_avoid'])) and !$this->InFooter and $this->AcceptPageBreak()) {
		$x=$this->x;//Current X position


		// WORD SPACING
		$ws=$this->ws;//Word Spacing
		$charspacing=$this->charspacing;//Character Spacing
		$this->ResetSpacing();

		$this->AddPage($this->CurOrientation);
		// Added to correct for OddEven Margins
		$x += $this->MarginCorrection;
		if ($currentx) { 
			$currentx += $this->MarginCorrection;
		} 
		$this->x=$x;
		// WORD SPACING
		$this->SetSpacing($charspacing,$ws);
	}

	// Test: to put line through centre of cell: $this->Line($this->x,$this->y+($h/2),$this->x+50,$this->y+($h/2));

/*-- COLUMNS --*/
	// COLS
	// COLUMN CHANGE
	if ($this->CurrCol != $oldcolumn) {
		if ($currentx) { 
			$currentx += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);
		} 
		$this->x += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);
	}

	// COLUMNS Update/overwrite the lowest bottom of printing y value for a column
	if ($this->ColActive) {
		if ($h) { $this->ColDetails[$this->CurrCol]['bottom_margin'] = $this->y+$h; }
		else { $this->ColDetails[$this->CurrCol]['bottom_margin'] = $this->y+$this->divheight; }
	}
/*-- END COLUMNS --*/

	// KEEP BLOCK TOGETHER Update/overwrite the lowest bottom of printing y value on first page
	if ($this->keep_block_together) {
		if ($h) { $this->ktBlock[$this->page]['bottom_margin'] = $this->y+$h; }
//		else { $this->ktBlock[$this->page]['bottom_margin'] = $this->y+$this->divheight; }
	}

	if($w==0) $w = $this->w-$this->rMargin-$this->x;
	$s='';
	if($fill==1 && $this->FillColor) { 
		if((isset($this->pageoutput[$this->page]['FillColor']) && $this->pageoutput[$this->page]['FillColor'] != $this->FillColor) || !isset($this->pageoutput[$this->page]['FillColor']) || $this->keep_block_together) { $s .= $this->FillColor.' '; }
		$this->pageoutput[$this->page]['FillColor'] = $this->FillColor;
	}


	$boxtop = $this->y;
	$boxheight = $h;
	$boxbottom = $this->y+$h;

	if($txt!='') {
		// FONT SIZE - this determines the baseline caculation
		if ($this->linemaxfontsize && !$this->processingHeader) { $bfs = $this->linemaxfontsize; }
		else  { $bfs = $this->FontSize; }
    		//Calculate baseline Superscript and Subscript Y coordinate adjustment
		$bfx = $this->baselineC;
    		$baseline = $bfx*$bfs;
		if($this->SUP) { $baseline += ($bfx-1.05)*$this->FontSize; }
		else if($this->SUB) { $baseline += ($bfx + 0.04)*$this->FontSize; }
		else if($this->bullet) { $baseline += ($bfx-0.7)*$this->FontSize; }

		// Vertical align (for Images)
		if ($abovefont || $belowfont) {	// from flowing block - valign always M
			$va = $abovefont + (0.5*$bfs);
		}
		else if ($this->lineheight_correction) { 
			if ($valign == 'T') { $va = (0.5 * $bfs * $this->lineheight_correction); }
			else if ($valign == 'B') { $va = $h-(0.5 * $bfs * $this->lineheight_correction); }
			else { $va = 0.5*$h; }	// Middle
		}
		else { 
			if ($valign == 'T') { $va = (0.5 * $bfs * $this->default_lineheight_correction); }
			else if ($valign == 'B') { $va = $h-(0.5 * $bfs * $this->default_lineheight_correction); }
			else { $va = 0.5*$h; }	// Middle
		}

		// ONLY SET THESE IF WANT TO CONFINE BORDER +/- FILL TO FIT FONTSIZE - NOT FULL CELL AS IS ORIGINAL FUNCTION
		// spanfill or spanborder are set in FlowingBlock functions
		if ($spanfill || !empty($this->spanborddet) || $link!='') {
			$exth = 0.2;	// Add to fontsize to increase height of background / link / border
			$boxtop = $this->y+$baseline+$va-($this->FontSize*(1+$exth/2)*(0.5+$bfx));
			$boxheight = $this->FontSize * (1+$exth);
			$boxbottom = $boxtop + $boxheight;
		}
	}

	$bbw = $tbw = $lbw = $rbw = 0;	// Border widths
	if (!empty($this->spanborddet)) { 
		if (!isset($this->spanborddet['B'])) { $this->spanborddet['B'] = array('s' => 0, 'style' => '', 'w' => 0); }
		if (!isset($this->spanborddet['T'])) { $this->spanborddet['T'] = array('s' => 0, 'style' => '', 'w' => 0); }
		if (!isset($this->spanborddet['L'])) { $this->spanborddet['L'] = array('s' => 0, 'style' => '', 'w' => 0); }
		if (!isset($this->spanborddet['R'])) { $this->spanborddet['R'] = array('s' => 0, 'style' => '', 'w' => 0); }
		$bbw = $this->spanborddet['B']['w'];
		$tbw = $this->spanborddet['T']['w'];
		$lbw = $this->spanborddet['L']['w'];
		$rbw = $this->spanborddet['R']['w'];
	}
	if($fill==1 || $border==1 || !empty($this->spanborddet)) {
		if (!empty($this->spanborddet)) { 
			if ($fill==1) {
				$s.=sprintf('%.3F %.3F %.3F %.3F re f ',($this->x-$lbw)*_MPDFK,($this->h-$boxtop+$tbw)*_MPDFK,($w+$lbw+$rbw)*_MPDFK,(-$boxheight-$tbw-$bbw)*_MPDFK);
			}
			$s.= ' q ';
			$dashon = 3;
			$dashoff = 3.5;
			$dot = 2.5;
			if($tbw) {
				$short = 0;
				if ($this->spanborddet['T']['style'] == 'dashed') {
					$s.=sprintf(' 0 j 0 J [%.3F %.3F] 0 d ',$tbw*$dashon*_MPDFK,$tbw*$dashoff*_MPDFK);
				}
				else if ($this->spanborddet['T']['style'] == 'dotted') {
					$s.=sprintf(' 1 j 1 J [%.3F %.3F] %.3F d ',0.001,$tbw*$dot*_MPDFK,-$tbw/2*_MPDFK);
					$short = $tbw/2;
				}
				else {
					$s.=' 0 j 0 J [] 0 d ';
				}
				$c = $this->SetDColor($this->spanborddet['T']['c'],true);
				if ($this->spanborddet['T']['style'] == 'double') {
					$s.=sprintf(' %s %.3F w ',$c,$tbw/3*_MPDFK);
					$xadj = $xadj2 = 0; 
					if ($this->spanborddet['L']['style'] == 'double') { $xadj = $this->spanborddet['L']['w']*2/3; }
					if ($this->spanborddet['R']['style'] == 'double') { $xadj2 = $this->spanborddet['R']['w']*2/3; }
					$s.=sprintf('%.3F %.3F m %.3F %.3F l S ',($this->x-$lbw)*_MPDFK,($this->h-$boxtop+$tbw*5/6)*_MPDFK,($this->x+$w+$rbw-$short)*_MPDFK,($this->h-$boxtop+$tbw*5/6)*_MPDFK);
					$s.=sprintf('%.3F %.3F m %.3F %.3F l S ',($this->x-$lbw+$xadj)*_MPDFK,($this->h-$boxtop+$tbw/6)*_MPDFK,($this->x+$w+$rbw-$short-$xadj2)*_MPDFK,($this->h-$boxtop+$tbw/6)*_MPDFK);
				}
				else {
					$s.=sprintf(' %s %.3F w ',$c,$tbw*_MPDFK);
					$s.=sprintf('%.3F %.3F m %.3F %.3F l S ',($this->x-$lbw)*_MPDFK,($this->h-$boxtop+$tbw/2)*_MPDFK,($this->x+$w+$rbw-$short)*_MPDFK,($this->h-$boxtop+$tbw/2)*_MPDFK);
				}
			}
			if($bbw) {
				$short = 0;
				if ($this->spanborddet['B']['style'] == 'dashed') {
					$s.=sprintf(' 0 j 0 J [%.3F %.3F] 0 d ',$bbw*$dashon*_MPDFK,$bbw*$dashoff*_MPDFK);
				}
				else if ($this->spanborddet['B']['style'] == 'dotted') {
					$s.=sprintf(' 1 j 1 J [%.3F %.3F] %.3F d ',0.001,$bbw*$dot*_MPDFK,-$bbw/2*_MPDFK);
					$short = $bbw/2;
				}
				else {
					$s.=' 0 j 0 J [] 0 d ';
				}
				$c = $this->SetDColor($this->spanborddet['B']['c'],true);
				if ($this->spanborddet['B']['style'] == 'double') {
					$s.=sprintf(' %s %.3F w ',$c,$bbw/3*_MPDFK);
					$xadj = $xadj2 = 0; 
					if ($this->spanborddet['L']['style'] == 'double') { $xadj = $this->spanborddet['L']['w']*2/3; }
					if ($this->spanborddet['R']['style'] == 'double') { $xadj2 = $this->spanborddet['R']['w']*2/3; }
					$s.=sprintf('%.3F %.3F m %.3F %.3F l S ',($this->x-$lbw+$xadj)*_MPDFK,($this->h-$boxbottom-$bbw/6)*_MPDFK,($this->x+$w+$rbw-$short-$xadj2)*_MPDFK,($this->h-$boxbottom-$bbw/6)*_MPDFK);
					$s.=sprintf('%.3F %.3F m %.3F %.3F l S ',($this->x-$lbw)*_MPDFK,($this->h-$boxbottom-$bbw*5/6)*_MPDFK,($this->x+$w+$rbw-$short)*_MPDFK,($this->h-$boxbottom-$bbw*5/6)*_MPDFK);
				}
				else {
					$s.=sprintf(' %s %.3F w ',$c,$bbw*_MPDFK);
					$s.=sprintf('%.3F %.3F m %.3F %.3F l S ',($this->x-$lbw)*_MPDFK,($this->h-$boxbottom-$bbw/2)*_MPDFK,($this->x+$w+$rbw-$short)*_MPDFK,($this->h-$boxbottom-$bbw/2)*_MPDFK);
				}
			}
			if($lbw) {
				$short = 0;
				if ($this->spanborddet['L']['style'] == 'dashed') {
					$s.=sprintf(' 0 j 0 J [%.3F %.3F] 0 d ',$lbw*$dashon*_MPDFK,$lbw*$dashoff*_MPDFK);
				}
				else if ($this->spanborddet['L']['style'] == 'dotted') {
					$s.=sprintf(' 1 j 1 J [%.3F %.3F] %.3F d ',0.001,$lbw*$dot*_MPDFK,-$lbw/2*_MPDFK);
					$short = $lbw/2;
				}
				else {
					$s.=' 0 j 0 J [] 0 d ';
				}
				$c = $this->SetDColor($this->spanborddet['L']['c'],true);
				if ($this->spanborddet['L']['style'] == 'double') {
					$s.=sprintf(' %s %.3F w ',$c,$lbw/3*_MPDFK);
					$yadj = $yadj2 = 0; 
					if ($this->spanborddet['T']['style'] == 'double') { $yadj = $this->spanborddet['T']['w']*2/3; }
					if ($this->spanborddet['B']['style'] == 'double') { $yadj2 = $this->spanborddet['B']['w']*2/3; }
					$s.=sprintf('%.3F %.3F m %.3F %.3F l S ',($this->x-$lbw/6)*_MPDFK,($this->h-$boxtop+$tbw-$yadj)*_MPDFK,($this->x-$lbw/6)*_MPDFK,($this->h-$boxbottom-$bbw+$short+$yadj2)*_MPDFK);
					$s.=sprintf('%.3F %.3F m %.3F %.3F l S ',($this->x-$lbw*5/6)*_MPDFK,($this->h-$boxtop+$tbw)*_MPDFK,($this->x-$lbw*5/6)*_MPDFK,($this->h-$boxbottom-$bbw+$short)*_MPDFK);
				}
				else {
					$s.=sprintf(' %s %.3F w ',$c,$lbw*_MPDFK);
					$s.=sprintf('%.3F %.3F m %.3F %.3F l S ',($this->x-$lbw/2)*_MPDFK,($this->h-$boxtop+$tbw)*_MPDFK,($this->x-$lbw/2)*_MPDFK,($this->h-$boxbottom-$bbw+$short)*_MPDFK);
				}
			}
			if($rbw) {
				$short = 0;
				if ($this->spanborddet['R']['style'] == 'dashed') {
					$s.=sprintf(' 0 j 0 J [%.3F %.3F] 0 d ',$rbw*$dashon*_MPDFK,$rbw*$dashoff*_MPDFK);
				}
				else if ($this->spanborddet['R']['style'] == 'dotted') {
					$s.=sprintf(' 1 j 1 J [%.3F %.3F] %.3F d ',0.001,$rbw*$dot*_MPDFK,-$rbw/2*_MPDFK);
					$short = $rbw/2;
				}
				else {
					$s.=' 0 j 0 J [] 0 d ';
				}
				$c = $this->SetDColor($this->spanborddet['R']['c'],true);
				if ($this->spanborddet['R']['style'] == 'double') {
					$s.=sprintf(' %s %.3F w ',$c,$rbw/3*_MPDFK);
					$yadj = $yadj2 = 0; 
					if ($this->spanborddet['T']['style'] == 'double') { $yadj = $this->spanborddet['T']['w']*2/3; }
					if ($this->spanborddet['B']['style'] == 'double') { $yadj2 = $this->spanborddet['B']['w']*2/3; }
					$s.=sprintf('%.3F %.3F m %.3F %.3F l S ',($this->x+$w+$rbw/6)*_MPDFK,($this->h-$boxtop+$tbw-$yadj)*_MPDFK,($this->x+$w+$rbw/6)*_MPDFK,($this->h-$boxbottom-$bbw+$short+$yadj2)*_MPDFK);
					$s.=sprintf('%.3F %.3F m %.3F %.3F l S ',($this->x+$w+$rbw*5/6)*_MPDFK,($this->h-$boxtop+$tbw)*_MPDFK,($this->x+$w+$rbw*5/6)*_MPDFK,($this->h-$boxbottom-$bbw+$short)*_MPDFK);
				}
				else {
					$s.=sprintf(' %s %.3F w ',$c,$rbw*_MPDFK);
					$s.=sprintf('%.3F %.3F m %.3F %.3F l S ',($this->x+$w+$rbw/2)*_MPDFK,($this->h-$boxtop+$tbw)*_MPDFK,($this->x+$w+$rbw/2)*_MPDFK,($this->h-$boxbottom-$bbw+$short)*_MPDFK);
				}
			}
			$s.= ' Q ';
		}
		else {
			if ($fill==1) $op=($border==1) ? 'B' : 'f';
			else $op='S';
			$s.=sprintf('%.3F %.3F %.3F %.3F re %s ',$this->x*_MPDFK,($this->h-$boxtop)*_MPDFK,$w*_MPDFK,-$boxheight*_MPDFK,$op);
		}
	}

	if(is_string($border)) {
		$x=$this->x;
		$y=$this->y;
		if(is_int(strpos($border,'L')))
			$s.=sprintf('%.3F %.3F m %.3F %.3F l S ',$x*_MPDFK,($this->h-$boxtop)*_MPDFK,$x*_MPDFK,($this->h-($boxbottom))*_MPDFK);
		if(is_int(strpos($border,'T')))
			$s.=sprintf('%.3F %.3F m %.3F %.3F l S ',$x*_MPDFK,($this->h-$boxtop)*_MPDFK,($x+$w)*_MPDFK,($this->h-$boxtop)*_MPDFK);
		if(is_int(strpos($border,'R')))
			$s.=sprintf('%.3F %.3F m %.3F %.3F l S ',($x+$w)*_MPDFK,($this->h-$boxtop)*_MPDFK,($x+$w)*_MPDFK,($this->h-($boxbottom))*_MPDFK);
		if(is_int(strpos($border,'B')))
			$s.=sprintf('%.3F %.3F m %.3F %.3F l S ',$x*_MPDFK,($this->h-($boxbottom))*_MPDFK,($x+$w)*_MPDFK,($this->h-($boxbottom))*_MPDFK);
	}

	if($txt!='') {
		if ($exactWidth)
			$stringWidth = $w;  
		else 
			$stringWidth = $this->GetStringWidth($txt) + ( $this->charspacing * mb_strlen( $txt, $this->mb_enc ) / _MPDFK )
				 + ( $this->ws * mb_substr_count( $txt, ' ', $this->mb_enc ) / _MPDFK );

		// Set x OFFSET FOR PRINTING
		if($align=='R') {
			$dx=$w-$this->cMarginR - $stringWidth - $lcpaddingR;
		}
		elseif($align=='C') {
			$dx=(($w - $stringWidth )/2);
		}
		elseif($align=='L' or $align=='J') $dx=$this->cMarginL + $lcpaddingL;
    		else $dx = 0;

		if($this->ColorFlag) $s .='q '.$this->TextColor.' ';

		// OUTLINE
		if($this->textparam['outline-s'] && !$this->S) {	// mPDF 5.6.07
			$s .=' '.sprintf('%.3F w',$this->LineWidth*_MPDFK).' ';
			$s .=" $this->DrawColor ";
			$s .=" 2 Tr ";
    		}
		else if ($this->falseBoldWeight && strpos($this->ReqFontStyle,"B") !== false && strpos($this->FontStyle,"B") === false && !$this->S) {	// can't use together with OUTLINE or Small Caps
			$s .= ' 2 Tr 1 J 1 j ';
			$s .= ' '.sprintf('%.3F w',($this->FontSize/130)*_MPDFK*$this->falseBoldWeight).' ';
			$tc = strtoupper($this->TextColor); // change 0 0 0 rg to 0 0 0 RG
			if($this->FillColor!=$tc) { $s .= ' '.$tc.' '; }		// stroke (outline) = same colour as text(fill)
		}
		else { $s .=" 0 Tr "; }	// mPDF 5.6.07

		if (strpos($this->ReqFontStyle,"I") !== false && strpos($this->FontStyle,"I") === false) {	// Artificial italic
			$aix = '1 0 0.261799 1 %.3F %.3F Tm '; 
		}
		else { $aix = '%.3F %.3F Td '; }

		// THE TEXT
		$sub = '';
		$this->CurrentFont['used']= true;

		// WORD SPACING
		// IF multibyte - Tw has no effect - need to use alternative method - do word spacing using an adjustment before each space
		if ($this->ws && !$this->usingCoreFont && !$this->CurrentFont['sip'] && !$this->CurrentFont['smp'] && !$this->S) {
		  $sub .= ' BT 0 Tw ET ';	 
		  if ($this->kerning && $this->useKerning) { $sub .= $this->_kern($txt, 'MBTw', $aix, ($this->x+$dx), ($this->y+$baseline+$va)); }
		  else {
			$space = " ";
			//Convert string to UTF-16BE without BOM
			$space= $this->UTF8ToUTF16BE($space , false);
			$space=$this->_escape($space ); 
			$sub .=sprintf('BT '.$aix,($this->x+$dx)*_MPDFK,($this->h-($this->y+$baseline+$va))*_MPDFK);
			$t = explode(' ',$txt);
			$sub .=sprintf(' %.3F Tc [',$this->charspacing);
			$numt = count($t);
			for($i=0;$i<$numt;$i++) {
				$tx = $t[$i]; 
				//Convert string to UTF-16BE without BOM
				$tx = $this->UTF8ToUTF16BE($tx , false);
				$tx = $this->_escape($tx); 
				$sub .=sprintf('(%s) ',$tx);
				if (($i+1)<$numt) {
					$adj = -($this->ws)*1000/$this->FontSizePt;
					$sub .=sprintf('%d(%s) ',$adj,$space);
				}
			}
			$sub .='] TJ ';
			$sub .=' ET';
		  }
		}
		else {
		  $txt2= $txt;
		  if ($this->CurrentFont['type']=='TTF' && ($this->CurrentFont['sip'] || $this->CurrentFont['smp'])) {
			if ($this->S) { $sub .= $this->_smallCaps($txt2, 'SIPSMP', $aix, $dx, _MPDFK, $baseline, $va); } 
			else {
				$txt2 = $this->UTF8toSubset($txt2);
				$sub .=sprintf('BT '.$aix.' %s Tj ET',($this->x+$dx)*_MPDFK,($this->h-($this->y+$baseline+$va))*_MPDFK,$txt2);
			}
		  }
		  else {
			if ($this->S) { $sub .= $this->_smallCaps($txt2, '', $aix, $dx, _MPDFK, $baseline, $va); } 
			else if ($this->kerning && $this->useKerning) { $sub .= $this->_kern($txt2, '', $aix, ($this->x+$dx), ($this->y+$baseline+$va)); } 
			else {
				if (!$this->usingCoreFont) {
					$txt2 = $this->UTF8ToUTF16BE($txt2, false);
				}
				$txt2=$this->_escape($txt2); 
				$sub .=sprintf('BT '.$aix.' (%s) Tj ET',($this->x+$dx)*_MPDFK,($this->h-($this->y+$baseline+$va))*_MPDFK,$txt2);
			}
		  }
		}
		// UNDERLINE
		if($this->U) {
			$c = strtoupper($this->TextColor); // change 0 0 0 rg to 0 0 0 RG
			if($this->FillColor!=$c) { $sub .= ' '.$c.' '; }
			if (isset($this->CurrentFont['up'])) { $up=$this->CurrentFont['up']; }
			else { $up = -100; }
			$adjusty = (-$up/1000* $this->FontSize);
 			if (isset($this->CurrentFont['ut'])) { $ut=$this->CurrentFont['ut']/1000* $this->FontSize; }
			else { $ut = 60/1000* $this->FontSize; }
			$olw = $this->LineWidth;
			$sub .=' '.(sprintf(' %.3F w 0 j 0 J ',$ut*_MPDFK));
			$sub .=' '.$this->_dounderline($this->x+$dx,$this->y+$baseline+$va+$adjusty,$txt);
			$sub .=' '.(sprintf(' %.3F w 2 j 2 J ',$olw*_MPDFK));
			if($this->FillColor!=$c) { $sub .= ' '.$this->FillColor.' '; }
		}

   		// STRIKETHROUGH
		if($this->strike) {
			$c = strtoupper($this->TextColor); // change 0 0 0 rg to 0 0 0 RG
			if($this->FillColor!=$c) { $sub .= ' '.$c.' '; }
    			//Superscript and Subscript Y coordinate adjustment (now for striked-through texts)
			if (isset($this->CurrentFont['desc']['CapHeight'])) { $ch=$this->CurrentFont['desc']['CapHeight']; }
			else { $ch = 700; }
			$adjusty = (-$ch/1000* $this->FontSize) * 0.35;
 			if (isset($this->CurrentFont['ut'])) { $ut=$this->CurrentFont['ut']/1000* $this->FontSize; }
			else { $ut = 60/1000* $this->FontSize; }
			$olw = $this->LineWidth;
			$sub .=' '.(sprintf(' %.3F w 0 j 0 J ',$ut*_MPDFK));
			$sub .=' '.$this->_dounderline($this->x+$dx,$this->y+$baseline+$va+$adjusty,$txt);
			$sub .=' '.(sprintf(' %.3F w 2 j 2 J ',$olw*_MPDFK));
			if($this->FillColor!=$c) { $sub .= ' '.$this->FillColor.' '; }
		}

		// TEXT SHADOW
		if ($this->textshadow) {		// First to process is last in CSS comma separated shadows
			foreach($this->textshadow AS $ts) {
					$s .= ' q ';
					$s .= $this->SetTColor($ts['col'], true)."\n";
					if ($ts['col']{0}==5 && ord($ts['col']{4})<100) {	// RGBa
						$s .= $this->SetAlpha(ord($ts['col']{4})/100, 'Normal', true, 'F')."\n"; 
					}
					else if ($ts['col']{0}==6 && ord($ts['col']{5})<100) {	// CMYKa
						$s .= $this->SetAlpha(ord($ts['col']{5})/100, 'Normal', true, 'F')."\n"; 
					}
					else if ($ts['col']{0}==1 && $ts['col']{2}==1 && ord($ts['col']{3})<100) {	// Gray
						$s .= $this->SetAlpha(ord($ts['col']{3})/100, 'Normal', true, 'F')."\n"; 
					}
					$s .= sprintf(' 1 0 0 1 %.4F %.4F cm', $ts['x']*_MPDFK, -$ts['y']*_MPDFK)."\n";
					$s .= $sub;
					$s .= ' Q ';
			}
		}

		$s .= $sub;

		// COLOR
		if($this->ColorFlag) $s .=' Q';

		// LINK
		if($link!='') {
			$this->Link($this->x,$boxtop,$w,$boxheight,$link);
		}
	}
	if($s) $this->_out($s);

	// WORD SPACING
	if ($this->ws && !$this->usingCoreFont) {
		$this->_out(sprintf('BT %.3F Tc ET',$this->charspacing));	 
	}
	$this->lasth=$h;
	if( strpos($txt,"\n") !== false) $ln=1; // cell recognizes \n from <BR> tag
	if($ln>0)
	{
		//Go to next line
		$this->y += $h;
		if($ln==1) {
			//Move to next line
			if ($currentx != 0) { $this->x=$currentx; }
			else { $this->x=$this->lMargin; }
   		}
	}
	else $this->x+=$w;


}


function _kern($txt, $mode, $aix, $x, $y) {
   if ($mode == 'MBTw') {	// Multibyte requiring word spacing
		  $space = ' ';
		  //Convert string to UTF-16BE without BOM
		  $space= $this->UTF8ToUTF16BE($space , false);
		  $space=$this->_escape($space ); 
		  $s = sprintf(' BT '.$aix,$x*_MPDFK,($this->h-$y)*_MPDFK);
		  $t = explode(' ',$txt);
		  for($i=0;$i<count($t);$i++) {
			$tx = $t[$i]; 

			$tj = '(';
			$unicode = $this->UTF8StringToArray($tx);
			for($ti=0;$ti<count($unicode);$ti++) {
				if ($ti > 0 && isset($this->CurrentFont['kerninfo'][$unicode[($ti-1)]][$unicode[$ti]]))  {
							$kern = -$this->CurrentFont['kerninfo'][$unicode[($ti-1)]][$unicode[$ti]];
							$tj .= sprintf(')%d(',$kern);
				}
				$tc = code2utf($unicode[$ti]);
				$tc = $this->UTF8ToUTF16BE($tc, false);
				$tj .= $this->_escape($tc); 
			}
			$tj .= ')';
			$s.=sprintf(' %.3F Tc [%s] TJ',$this->charspacing,$tj);


			if (($i+1)<count($t)) {
				$s.=sprintf(' %.3F Tc (%s) Tj',$this->ws+$this->charspacing,$space);
			}
		  }
		  $s.=' ET ';
   }
   else if (!$this->usingCoreFont) {
	$s = '';
	$tj = '(';
	$unicode = $this->UTF8StringToArray($txt);
	for($i=0;$i<count($unicode);$i++) {
		if ($i > 0 && isset($this->CurrentFont['kerninfo'][$unicode[($i-1)]][$unicode[$i]])) {
					$kern = -$this->CurrentFont['kerninfo'][$unicode[($i-1)]][$unicode[$i]];
					$tj .= sprintf(')%d(',$kern);
		}
		$tx = code2utf($unicode[$i]);
		$tx = $this->UTF8ToUTF16BE($tx, false);
		$tj .= $this->_escape($tx); 
	}
	$tj .= ')';
	$s.=sprintf(' BT '.$aix.' [%s] TJ ET ',$x*_MPDFK,($this->h-$y)*_MPDFK,$tj);
   }
   else {	// CORE Font
	$s = '';
	$tj = '(';
	$l = strlen($txt);
	for($i=0;$i<$l;$i++) {
		if ($i > 0 && isset($this->CurrentFont['kerninfo'][$txt[($i-1)]][$txt[$i]])) {
			$kern = -$this->CurrentFont['kerninfo'][$txt[($i-1)]][$txt[$i]];
			$tj .= sprintf(')%d(',$kern);
		}
		$tj .= $this->_escape($txt[$i]); 
	}
	$tj .= ')';
	$s.=sprintf(' BT '.$aix.' [%s] TJ ET ',$x*_MPDFK,($this->h-$y)*_MPDFK,$tj);
   }

   return $s;
}


function _smallCaps($txt, $mode, $aix, $dx, $k, $baseline, $va) {
	$upp = false;
	$str = array();
	$bits = array();
	if (!$this->usingCoreFont) { 
	   $unicode = $this->UTF8StringToArray($txt);
	   foreach($unicode as $char) {
		if ($this->ws && $char == 32) { 	// space
			if (count($str)) { $bits[] = array($upp, $str, false); }
			$bits[] = array(false, array(32), true); 
			$str = array(); 
			$upp = false;
		}
		else if (isset($this->upperCase[$char])) { 
			if (!$upp) { 
				if (count($str)) { $bits[] = array($upp, $str, false); }
				$str = array(); 
			}
			$str[] = $this->upperCase[$char]; 
			if ((!isset($this->CurrentFont['sip']) || !$this->CurrentFont['sip']) && (!isset($this->CurrentFont['smp']) || !$this->CurrentFont['smp'])) {	
				$this->CurrentFont['subset'][$this->upperCase[$char]] = $this->upperCase[$char];
			}
			$upp = true;
		}
		else { 
			if ($upp) { 
				if (count($str)) { $bits[] = array($upp, $str, false); }
				$str = array(); 
			}
			$str[] = $char;
			$upp = false;
		}
	   }
	}
	else {
	   for($i=0;$i<strlen($txt);$i++) {
		if (isset($this->upperCase[ord($txt[$i])]) && $this->upperCase[ord($txt[$i])] < 256) { 
			if (!$upp) { 
				if (count($str)) { $bits[] = array($upp, $str, false); }
				$str = array(); 
			}
			$str[] = $this->upperCase[ord($txt[$i])]; 
			$upp = true;
		}
		else { 
			if ($upp) { 
				if (count($str)) { $bits[] = array($upp, $str, false); }
				$str = array(); 
			}
			$str[] = ord($txt[$i]);
			$upp = false;
		}
	   }
	}
	if (count($str)) { $bits[] = array($upp, $str, false); }

	$fid = $this->CurrentFont['i'];

	$s=sprintf(' BT '.$aix,($this->x+$dx)*$k,($this->h-($this->y+$baseline+$va))*$k);
	foreach($bits AS $b) {
		if ($b[0]) { $upp = true; }
		else { $upp = false; }

		$size = count ($b[1]);
		$txt = '';
		for ($i = 0; $i < $size; $i++) {
			$txt .= code2utf($b[1][$i]); 
		}
		if ($this->usingCoreFont) { 
			$txt = utf8_decode($txt);
		}
		if ($mode == 'SIPSMP') {
			$txt = $this->UTF8toSubset($txt);
		}
		else { 
			if (!$this->usingCoreFont) {
				$txt = $this->UTF8ToUTF16BE($txt, false);
			}
			$txt=$this->_escape($txt); 
			$txt = '('.$txt.')';
		}
		if ($b[2]) { // space
			$s.=sprintf(' /F%d %.3F Tf %d Tz', $fid, $this->FontSizePt, 100); 
			$s.=sprintf(' %.3F Tc', ($this->charspacing+$this->ws));
			$s.=sprintf(' %s Tj', $txt);
		}
		else if ($upp) { 
			$s.=sprintf(' /F%d %.3F Tf', $fid, $this->FontSizePt*$this->smCapsScale); 
			$s.=sprintf(' %d Tz', $this->smCapsStretch); 
			$s.=sprintf(' %.3F Tc', ($this->charspacing*100/$this->smCapsStretch));
			$s.=sprintf(' %s Tj', $txt);
		}
		else { 
			$s.=sprintf(' /F%d %.3F Tf %d Tz', $fid, $this->FontSizePt, 100); 
			$s.=sprintf(' %.3F Tc', ($this->charspacing)); 
			$s.=sprintf(' %s Tj', $txt);
		}
	}
	$s.=' ET ';
	return $s;
}



function MultiCell($w,$h,$txt,$border=0,$align='',$fill=0,$link='',$directionality='ltr',$encoded=false)
{
	// Parameter (pre-)encoded - When called internally from ToC or textarea: mb_encoding already done - but not reverse RTL/Indic
	if (!$encoded) {
		$txt = $this->purify_utf8_text($txt);
		if ($this->text_input_as_HTML) {
			$txt = $this->all_entities_to_utf8($txt);
		}
		if ($this->usingCoreFont) { $txt = mb_convert_encoding($txt,$this->mb_enc,'UTF-8'); }
		// Font-specific ligature substitution for Indic fonts
		else if (isset($this->CurrentFont['indic']) && $this->CurrentFont['indic']) {	// *INDIC*
			$this->ConvertIndic($tmp);	// *INDIC*
		}	// *INDIC*
		if (preg_match("/([".$this->pregRTLchars."])/u", $txt)) { $this->biDirectional = true; }	// *RTL*
	}
	if (!$align) { $align = $this->defaultAlign; }

	//Output text with automatic or explicit line breaks
	$cw=&$this->CurrentFont['cw'];
	if($w==0)	$w=$this->w-$this->rMargin-$this->x;

	$wmax = ($w - ($this->cMarginL+$this->cMarginR));
	if ($this->usingCoreFont)  {
		$s=str_replace("\r",'',$txt);
		$nb=strlen($s);
		while($nb>0 and $s[$nb-1]=="\n")	$nb--;
	}
	else {
		$s=str_replace("\r",'',$txt);
		$nb=mb_strlen($s, $this->mb_enc );
		while($nb>0 and mb_substr($s,$nb-1,1,$this->mb_enc )=="\n")	$nb--;
	}
	$b=0;
	if($border) {
		if($border==1) {
			$border='LTRB';
			$b='LRT';
			$b2='LR';
		}
		else {
			$b2='';
			if(is_int(strpos($border,'L')))	$b2.='L';
			if(is_int(strpos($border,'R')))	$b2.='R';
			$b=is_int(strpos($border,'T')) ? $b2.'T' : $b2;
		}
	}
	$sep=-1;
	$i=0;
	$j=0;
	$l=0;
	$ns=0;
	$nl=1;



   if (!$this->usingCoreFont)  {
	$checkCursive=false;
	if ($this->biDirectional) {  $checkCursive=true; }
	else if (isset($this->CurrentFont['indic']) && $this->CurrentFont['indic']) {  $checkCursive=true; }	// *INDIC*
	while($i<$nb) {
		//Get next character
		$c = mb_substr($s,$i,1,$this->mb_enc );
		if($c == "\n") {
			//Explicit line break
			// WORD SPACING
			$this->ResetSpacing();
			$tmp = rtrim(mb_substr($s,$j,$i-$j,$this->mb_enc));
			// DIRECTIONALITY
			$this->magic_reverse_dir($tmp, true, $directionality);	// *RTL*

			$this->Cell($w,$h,$tmp,$b,2,$align,$fill,$link);
			$i++;
			$sep=-1;
			$j=$i;
			$l=0;
			$ns=0;
			$nl++;
			if($border and $nl==2) $b=$b2;
			continue;
		}
		if($c == " ") {
			$sep=$i;
			$ls=$l;
			$ns++;
		}

		$l += $this->GetCharWidthNonCore($c);

		if($l>$wmax) {
			//Automatic line break
			if($sep==-1) {	// Only one word
				if($i==$j) $i++;
				// WORD SPACING
				$this->ResetSpacing();
				$tmp = rtrim(mb_substr($s,$j,$i-$j,$this->mb_enc));
				// DIRECTIONALITY
				$this->magic_reverse_dir($tmp, true, $directionality);	// *RTL*

				$this->Cell($w,$h,$tmp,$b,2,$align,$fill,$link);
			}
			else {
				$tmp = rtrim(mb_substr($s,$j,$sep-$j,$this->mb_enc));
				if($align=='J') {
					//////////////////////////////////////////
					// JUSTIFY J using Unicode fonts (Word spacing doesn't work)
					// WORD SPACING UNICODE
					// Change NON_BREAKING SPACE to spaces so they are 'spaced' properly
					$tmp = str_replace(chr(194).chr(160),chr(32),$tmp ); 
					$len_ligne = $this->GetStringWidth($tmp );
					$nb_carac = mb_strlen( $tmp , $this->mb_enc ) ;  
					$nb_spaces = mb_substr_count( $tmp ,' ', $this->mb_enc ) ;  

					$inclCursive=false;
					if ($checkCursive) {
						if (preg_match("/([".$this->pregRTLchars."])/u", $tmp)) { $inclCursive = true; }	// *RTL*
						if (preg_match("/([".$this->pregHIchars.$this->pregBNchars.$this->pregPAchars."])/u", $tmp)) { $inclCursive = true; }	// *INDIC*
					}
					list($charspacing,$ws) = $this->GetJspacing($nb_carac,$nb_spaces,((($wmax) - $len_ligne) * _MPDFK),$inclCursive);
					$this->SetSpacing($charspacing,$ws);
					//////////////////////////////////////////
				}

				// DIRECTIONALITY
				$this->magic_reverse_dir($tmp, true, $directionality);	// *RTL*

				$this->Cell($w,$h,$tmp,$b,2,$align,$fill,$link);
				$i=$sep+1;
			}
			$sep=-1;
			$j=$i;
			$l=0;
			$ns=0;
			$nl++;
			if($border and $nl==2) $b=$b2;
		}
		else $i++;
	}
	//Last chunk
	// WORD SPACING

	$this->ResetSpacing();

   }


   else {

	while($i<$nb) {
		//Get next character
		$c=$s[$i];
		if($c == "\n") {
			//Explicit line break
			// WORD SPACING
			$this->ResetSpacing();
			$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill,$link);
			$i++;
			$sep=-1;
			$j=$i;
			$l=0;
			$ns=0;
			$nl++;
			if($border and $nl==2) $b=$b2;
			continue;
		}
		if($c == " ") {
			$sep=$i;
			$ls=$l;
			$ns++;
		}

		$l += $this->GetCharWidthCore($c);
		if($l>$wmax) {
			//Automatic line break
			if($sep==-1) {
				if($i==$j) $i++;
				// WORD SPACING
				$this->ResetSpacing();
				$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill,$link);
			}
			else {
				if($align=='J') {
					$tmp = rtrim(substr($s,$j,$sep-$j));
					//////////////////////////////////////////
					// JUSTIFY J using Unicode fonts (Word spacing doesn't work)
					// WORD SPACING NON_UNICDOE/CJK
					// Change NON_BREAKING SPACE to spaces so they are 'spaced' properly
					$tmp = str_replace(chr(160),chr(32),$tmp);
					$len_ligne = $this->GetStringWidth($tmp );
					$nb_carac = strlen( $tmp ) ;  
					$nb_spaces = substr_count( $tmp ,' ' ) ;  
					list($charspacing,$ws) = $this->GetJspacing($nb_carac,$nb_spaces,((($wmax) - $len_ligne) * _MPDFK),false);
					$this->SetSpacing($charspacing,$ws);
					//////////////////////////////////////////
				}
				$this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill,$link);
				$i=$sep+1;
			}
			$sep=-1;
			$j=$i;
			$l=0;
			$ns=0;
			$nl++;
			if($border and $nl==2) $b=$b2;
		}
		else $i++;
	}
	//Last chunk
	// WORD SPACING

	$this->ResetSpacing();

   }
	//Last chunk
   if($border and is_int(strpos($border,'B')))	$b.='B';
   if (!$this->usingCoreFont)  {
		$tmp = rtrim(mb_substr($s,$j,$i-$j,$this->mb_enc));
		// DIRECTIONALITY
		$this->magic_reverse_dir($tmp, true, $directionality);	// *RTL*
   		$this->Cell($w,$h,$tmp,$b,2,$align,$fill,$link);
   }
   else { $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill,$link); }
   $this->x=$this->lMargin;
}


/*-- DIRECTW --*/
function Write($h,$txt,$currentx=0,$link='',$directionality='ltr',$align='') {
	if (!class_exists('directw', false)) { include(_MPDF_PATH.'classes/directw.php'); }
	if (empty($this->directw)) { $this->directw = new directw($this); }
	$this->directw->Write($h,$txt,$currentx,$link,$directionality,$align);
}
/*-- END DIRECTW --*/


/*-- HTML-CSS --*/
function saveInlineProperties() {
	$saved = array();
	$saved[ 'family' ] = $this->FontFamily;
	$saved[ 'style' ] = $this->FontStyle;
	$saved[ 'sizePt' ] = $this->FontSizePt;
	$saved[ 'size' ] = $this->FontSize;
	$saved[ 'HREF' ] = $this->HREF; 
	$saved[ 'underline' ] = $this->U; 
	$saved[ 'smCaps' ] = $this->S;
	$saved[ 'strike' ] = $this->strike;
	$saved[ 'textshadow' ] = $this->textshadow;
	$saved[ 'SUP' ] = $this->SUP; 
	$saved[ 'SUB' ] = $this->SUB; 
	$saved[ 'linewidth' ] = $this->LineWidth;
	$saved[ 'drawcolor' ] = $this->DrawColor;
	$saved[ 'textparam' ] = $this->textparam;
	$saved[ 'toupper' ] = $this->toupper;
	$saved[ 'tolower' ] = $this->tolower;
	$saved[ 'capitalize' ] = $this->capitalize;
	$saved[ 'fontkerning' ] = $this->kerning;
	$saved[ 'lSpacingCSS' ] = $this->lSpacingCSS;
	$saved[ 'wSpacingCSS' ] = $this->wSpacingCSS;
	$saved[ 'I' ] = $this->I;
	$saved[ 'B' ] = $this->B;
	$saved[ 'colorarray' ] = $this->colorarray;
	$saved[ 'bgcolorarray' ] = $this->spanbgcolorarray;
	$saved[ 'border' ] = $this->spanborddet;
	$saved[ 'color' ] = $this->TextColor; 
	$saved[ 'bgcolor' ] = $this->FillColor;
	$saved[ 'lang' ] = $this->currentLang;
	$saved[ 'display_off' ] = $this->inlineDisplayOff;

	return $saved;
}

function restoreInlineProperties( &$saved) {
	$FontFamily = $saved[ 'family' ];
	$this->FontStyle = $saved[ 'style' ];
	$this->FontSizePt = $saved[ 'sizePt' ];
	$this->FontSize = $saved[ 'size' ];

	$this->currentLang =  $saved['lang'];
	if ($this->useLang && !$this->usingCoreFont) {
	  if ($this->currentLang != $this->default_lang && ((strlen($this->currentLang) == 5 && $this->currentLang != 'UTF-8') || strlen($this->currentLang ) == 2)) { 
		list ($coreSuitable,$mpdf_pdf_unifonts) = GetLangOpts($this->currentLang, $this->useAdobeCJK);
		if ($mpdf_pdf_unifonts) { $this->RestrictUnicodeFonts($mpdf_pdf_unifonts); }
		else { $this->RestrictUnicodeFonts($this->default_available_fonts ); }
	  }
	  else { 
		$this->RestrictUnicodeFonts($this->default_available_fonts );
	  } 
	}

	$this->ColorFlag = ($this->FillColor != $this->TextColor); //Restore ColorFlag as well

	$this->HREF = $saved[ 'HREF' ];
	$this->U = $saved[ 'underline' ];
	$this->S = $saved[ 'smCaps' ];
	$this->strike = $saved[ 'strike' ];
	$this->textshadow = $saved[ 'textshadow' ];
	$this->SUP = $saved[ 'SUP' ];
	$this->SUB = $saved[ 'SUB' ];
	$this->LineWidth = $saved[ 'linewidth' ];
	$this->DrawColor = $saved[ 'drawcolor' ];
	$this->textparam = $saved[ 'textparam' ];
	$this->inlineDisplayOff = $saved['display_off'];

	$this->toupper = $saved[ 'toupper' ];
	$this->tolower = $saved[ 'tolower' ];
	$this->capitalize = $saved[ 'capitalize' ];
	$this->kerning = $saved[ 'fontkerning' ];
	$this->lSpacingCSS = $saved[ 'lSpacingCSS' ];
	if (($this->lSpacingCSS || $this->lSpacingCSS==='0') && strtoupper($this->lSpacingCSS) != 'NORMAL') {
		$this->fixedlSpacing = $this->ConvertSize($this->lSpacingCSS,$this->FontSize);
	}
	else { $this->fixedlSpacing = false; }
	$this->wSpacingCSS = $saved[ 'wSpacingCSS' ];
	if ($this->wSpacingCSS && strtoupper($this->wSpacingCSS) != 'NORMAL') { 
		$this->minwSpacing = $this->ConvertSize($this->wSpacingCSS,$this->FontSize);
	}
	else { $this->minwSpacing = 0; }
  
	$this->SetFont($FontFamily, $saved[ 'style' ].($this->U ? 'U' : '').($this->S ? 'S' : ''),$saved[ 'sizePt' ],false);

	$this->currentfontstyle = $saved[ 'style' ].($this->U ? 'U' : '').($this->S ? 'S' : '');
	$this->currentfontsize = $saved[ 'sizePt' ];
	$this->SetStylesArray(array('S'=>$this->S, 'U'=>$this->U, 'B'=>$saved[ 'B' ], 'I'=>$saved[ 'I' ]));

	$this->TextColor = $saved[ 'color' ];
	$this->FillColor = $saved[ 'bgcolor' ];
	$this->colorarray = $saved[ 'colorarray' ];
	$cor = $saved[ 'colorarray' ];
	if ($cor) $this->SetTColor($cor);
	$this->spanbgcolorarray = $saved[ 'bgcolorarray' ];
	$cor = $saved[ 'bgcolorarray' ];
	if ($cor) $this->SetFColor($cor);
	$this->spanborddet = $saved[ 'border' ];
}



// Used when ColActive for tables - updated to return first block with background fill OR borders
function GetFirstBlockFill() {
	// Returns the first blocklevel that uses a bgcolor fill
	$startfill = 0;
	for ($i=1;$i<=$this->blklvl;$i++) {
		if ($this->blk[$i]['bgcolor'] || $this->blk[$i]['border_left']['w'] || $this->blk[$i]['border_right']['w']  || $this->blk[$i]['border_top']['w']  || $this->blk[$i]['border_bottom']['w']  ) {
			$startfill = $i;
			break;
		}
	}
	return $startfill;
}

function SetBlockFill($blvl) {
	if ($this->blk[$blvl]['bgcolor']) {
		$this->SetFColor($this->blk[$blvl]['bgcolorarray']);
		return 1;
	}
	else {
		$this->SetFColor($this->ConvertColor(255));
		return 0;
	}
}


//-------------------------FLOWING BLOCK------------------------------------//
//The following functions were originally written by Damon Kohler           //
//--------------------------------------------------------------------------//

function saveFont() {
	$saved = array();
	$saved[ 'family' ] = $this->FontFamily;
	$saved[ 'style' ] = $this->FontStyle;
	$saved[ 'sizePt' ] = $this->FontSizePt;
	$saved[ 'size' ] = $this->FontSize;
	$saved[ 'curr' ] = &$this->CurrentFont;
	$saved[ 'color' ] = $this->TextColor; 
	$saved[ 'spanbgcolor' ] = $this->spanbgcolor; 
	$saved[ 'spanbgcolorarray' ] = $this->spanbgcolorarray; 
	$saved[ 'bord' ] = $this->spanborder;
	$saved[ 'border' ] = $this->spanborddet;
	$saved[ 'HREF' ] = $this->HREF;
	$saved[ 'underline' ] = $this->U; 
	$saved[ 'smCaps' ] = $this->S;
	$saved[ 'strike' ] = $this->strike;
	$saved[ 'textshadow' ] = $this->textshadow;
	$saved[ 'SUP' ] = $this->SUP;
	$saved[ 'SUB' ] = $this->SUB;
	$saved[ 'linewidth' ] = $this->LineWidth;
	$saved[ 'drawcolor' ] = $this->DrawColor;
	$saved[ 'textparam' ] = $this->textparam;
	$saved[ 'ReqFontStyle' ] = $this->ReqFontStyle;
	$saved[ 'fontkerning' ] = $this->kerning; 
	$saved[ 'fixedlSpacing' ] = $this->fixedlSpacing;
	$saved[ 'minwSpacing' ] = $this->minwSpacing;
	return $saved;
}

function restoreFont( &$saved, $write=true) {
	if (!isset($saved) || empty($saved)) return;

	$this->FontFamily = $saved[ 'family' ];
	$this->FontStyle = $saved[ 'style' ];
	$this->FontSizePt = $saved[ 'sizePt' ];
	$this->FontSize = $saved[ 'size' ];
	$this->CurrentFont = &$saved[ 'curr' ];
	$this->TextColor = $saved[ 'color' ]; 
	$this->spanbgcolor = $saved[ 'spanbgcolor' ]; 
	$this->spanbgcolorarray = $saved[ 'spanbgcolorarray' ]; 
	$this->spanborder = $saved[ 'bord' ];
	$this->spanborddet = $saved[ 'border' ];
	$this->ColorFlag = ($this->FillColor != $this->TextColor); //Restore ColorFlag as well
	$this->HREF = $saved[ 'HREF' ]; 
	$this->U = $saved[ 'underline' ]; 
	$this->S = $saved[ 'smCaps' ];
	$this->kerning = $saved[ 'fontkerning' ];
	$this->fixedlSpacing = $saved[ 'fixedlSpacing' ];
	$this->minwSpacing = $saved[ 'minwSpacing' ];
	$this->strike = $saved[ 'strike' ]; 
	$this->textshadow = $saved[ 'textshadow' ];
	$this->SUP = $saved[ 'SUP' ]; 
	$this->SUB = $saved[ 'SUB' ]; 
	$this->LineWidth = $saved[ 'linewidth' ]; 
	$this->DrawColor = $saved[ 'drawcolor' ]; 
	$this->textparam = $saved[ 'textparam' ];
	if ($write) { 
		$this->SetFont($saved[ 'family' ],$saved[ 'style' ].($this->U ? 'U' : '').($this->S ? 'S' : ''),$saved[ 'sizePt' ],true,true);	// force output
		$fontout = (sprintf('BT /F%d %.3F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
		if($this->page>0 && ((isset($this->pageoutput[$this->page]['Font']) && $this->pageoutput[$this->page]['Font'] != $fontout) || !isset($this->pageoutput[$this->page]['Font']) || $this->keep_block_together)) { $this->_out($fontout); }
		$this->pageoutput[$this->page]['Font'] = $fontout;
	}
	else 
		$this->SetFont($saved[ 'family' ],$saved[ 'style' ].($this->U ? 'U' : '').($this->S ? 'S' : ''),$saved[ 'sizePt' ]);
	$this->ReqFontStyle = $saved[ 'ReqFontStyle' ];
}

function newFlowingBlock( $w, $h, $a = '', $is_table = false, $is_list = false, $blockstate = 0, $newblock=true, $blockdir='ltr')
{
	if (!$a) { 
		if ($blockdir=='rtl') { $a = 'R'; }
		else { $a = 'L'; }
	}
	$this->flowingBlockAttr[ 'width' ] = ($w * _MPDFK);
	// line height in user units
	$this->flowingBlockAttr[ 'is_table' ] = $is_table;
	$this->flowingBlockAttr[ 'is_list' ] = $is_list;
	$this->flowingBlockAttr[ 'height' ] = $h;
	$this->flowingBlockAttr[ 'lineCount' ] = 0;
	$this->flowingBlockAttr[ 'align' ] = $a;
	$this->flowingBlockAttr[ 'font' ] = array();
	$this->flowingBlockAttr[ 'content' ] = array();
	$this->flowingBlockAttr[ 'contentB' ] = array();
	$this->flowingBlockAttr[ 'contentWidth' ] = 0;
	$this->flowingBlockAttr[ 'blockstate' ] = $blockstate;

	$this->flowingBlockAttr[ 'newblock' ] = $newblock;
	$this->flowingBlockAttr[ 'valign' ] = 'M';
	$this->flowingBlockAttr['blockdir'] = $blockdir;

}

function finishFlowingBlock($endofblock=false, $next='') {
	$currentx = $this->x;
	//prints out the last chunk
	$is_table = $this->flowingBlockAttr[ 'is_table' ];
	$is_list = $this->flowingBlockAttr[ 'is_list' ];
	$maxWidth =& $this->flowingBlockAttr[ 'width' ];
	$lineHeight =& $this->flowingBlockAttr[ 'height' ];
	$align =& $this->flowingBlockAttr[ 'align' ];
	$content =& $this->flowingBlockAttr[ 'content' ];
	$contentB =& $this->flowingBlockAttr[ 'contentB' ];
	$font =& $this->flowingBlockAttr[ 'font' ];
	$contentWidth =& $this->flowingBlockAttr[ 'contentWidth' ];
	$lineCount =& $this->flowingBlockAttr[ 'lineCount' ];
	$valign =& $this->flowingBlockAttr[ 'valign' ];
	$blockstate = $this->flowingBlockAttr[ 'blockstate' ];

	$newblock = $this->flowingBlockAttr[ 'newblock' ];
	$blockdir = $this->flowingBlockAttr['blockdir'];


	// *********** BLOCK BACKGROUND COLOR *****************//
	if ($this->blk[$this->blklvl]['bgcolor'] && !$is_table) {
		$fill = 0;
	}
	else {
		$this->SetFColor($this->ConvertColor(255));
		$fill = 0;
	}

	$hanger = '';	// mPDF 5.6.40

	// Always right trim!
	// Right trim content and adjust width if need to justify (later)
		if (isset($content[count($content)-1]) && preg_match('/[ ]+$/',$content[count($content)-1], $m)) {
			$strip = strlen($m[0]);
			$content[count($content)-1] = substr($content[count($content)-1],0,(strlen($content[count($content)-1])-$strip));
			$this->restoreFont( $font[ count($content)-1 ],false );
			$contentWidth -= $this->GetStringWidth($m[0]) * _MPDFK;
		}

	// the amount of space taken up so far in user units
	$usedWidth = 0;

	// COLS
	$oldcolumn = $this->CurrCol;

	if ($this->ColActive && !$is_table) { $this->breakpoints[$this->CurrCol][] = $this->y; }	// *COLUMNS*

	// Print out each chunk

/*-- TABLES --*/
	if ($is_table) { 
		$ipaddingL = 0; 
		$ipaddingR = 0; 
		$paddingL = 0;
		$paddingR = 0;
	} 
	else { 
/*-- END TABLES --*/
		$ipaddingL = $this->blk[$this->blklvl]['padding_left']; 
		$ipaddingR = $this->blk[$this->blklvl]['padding_right']; 
		$paddingL = ($ipaddingL * _MPDFK); 
		$paddingR = ($ipaddingR * _MPDFK);
		$this->cMarginL =  $this->blk[$this->blklvl]['border_left']['w'];
		$this->cMarginR =  $this->blk[$this->blklvl]['border_right']['w'];

		// Added mPDF 3.0 Float DIV
		$fpaddingR = 0;
		$fpaddingL = 0;
/*-- CSS-FLOAT --*/
		if (count($this->floatDivs)) {
			list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl);
			if ($r_exists) { $fpaddingR = $r_width; }
			if ($l_exists) { $fpaddingL = $l_width; }
		}
/*-- END CSS-FLOAT --*/

		$usey = $this->y + 0.002;
		if (($newblock) && ($blockstate==1 || $blockstate==3) && ($lineCount == 0) ) { 
			$usey += $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'];
		}
/*-- CSS-IMAGE-FLOAT --*/
		// If float exists at this level
		if (isset($this->floatmargins['R']) && $usey <= $this->floatmargins['R']['y1'] && $usey >= $this->floatmargins['R']['y0'] && !$this->floatmargins['R']['skipline']) { $fpaddingR += $this->floatmargins['R']['w']; }
		if (isset($this->floatmargins['L']) && $usey <= $this->floatmargins['L']['y1'] && $usey >= $this->floatmargins['L']['y0'] && !$this->floatmargins['L']['skipline']) { $fpaddingL += $this->floatmargins['L']['w']; }
/*-- END CSS-IMAGE-FLOAT --*/
	}	// *TABLES*

		// Set Current lineheight (correction factor)
		$lhfixed = false; 
/*-- LISTS --*/
		if ($is_list) {
			if (preg_match('/([0-9.,]+)mm/',$this->list_lineheight[$this->listlvl][$this->listOcc],$am)) { 
				$lhfixed = true; 
				$def_fontsize = $this->InlineProperties['LISTITEM'][$this->listlvl][$this->listOcc][$this->listnum]['size'];
				$this->lineheight_correction = $am[1] / $def_fontsize ;
			}
			else { 
				$this->lineheight_correction = $this->list_lineheight[$this->listlvl][$this->listOcc]; 
			}
		}
		else
/*-- END LISTS --*/
/*-- TABLES --*/
		if ($is_table) {
			if (preg_match('/([0-9.,]+)mm/',$this->table_lineheight,$am)) { 
				$lhfixed = true; 
				$def_fontsize = $this->FontSize; 				// needs to be default font-size for block ****
				$this->lineheight_correction = $lineHeight / $def_fontsize ; 
			}
			else { 
				$this->lineheight_correction = $this->table_lineheight; 
			}
		}
		else
/*-- END TABLES --*/
		if (isset($this->blk[$this->blklvl]['line_height']) && $this->blk[$this->blklvl]['line_height']) {
			if (preg_match('/([0-9.,]+)mm/',$this->blk[$this->blklvl]['line_height'],$am)) { 
				$lhfixed = true; 
				$def_fontsize = $this->blk[$this->blklvl]['InlineProperties']['size']; 	// needs to be default font-size for block ****
				$this->lineheight_correction = $am[1] / $def_fontsize ;
			}
			else { 
				$this->lineheight_correction = $this->blk[$this->blklvl]['line_height']; 
			}
		} 
		else {
			$this->lineheight_correction = $this->normalLineheight; 
		}

		//  correct lineheight to maximum fontsize
		if ($lhfixed) { $maxlineHeight = $this->lineheight; }
		else { $maxlineHeight = 0; }
		$this->forceExactLineheight = true;
		$maxfontsize = 0;
		// While we're at it, check if contains cursive text
		$checkCursive=false;
		if ($this->biDirectional) {  $checkCursive=true; }	// *RTL*
		foreach ( $content as $k => $chunk )
		{
		  $this->restoreFont( $font[ $k ],false );
		  if (!isset($this->objectbuffer[$k])) { 
			// Soft Hyphens chr(173)
			if (!$this->usingCoreFont) {
			      $content[$k] = $chunk = str_replace("\xc2\xad",'',$chunk ); 
				if (isset($this->CurrentFont['indic']) && $this->CurrentFont['indic']) {  $checkCursive=true; }	// *INDIC*
			}
			else if ($this->FontFamily!='csymbol' && $this->FontFamily!='czapfdingbats') {
			      $content[$k] = $chunk = str_replace(chr(173),'',$chunk );
			}
			// Special case of sub/sup carried over on its own to last line
			if (($this->SUB || $this->SUP) && count($content)==1) { $actfs = $this->FontSize*100/55; } // 55% is font change for sub/sup
			else { $actfs = $this->FontSize; }
			if (!$lhfixed) { $maxlineHeight = max($maxlineHeight,$actfs * $this->lineheight_correction ); }
			if ($lhfixed && ($actfs > $def_fontsize || ($actfs > ($lineHeight * $this->lineheight_correction) && $is_list))) { 
				$this->forceExactLineheight = false; 
			}
			$maxfontsize = max($maxfontsize,$actfs);
		  }
		}

		if(isset($font[count($font)-1])) {
			$lastfontreqstyle = $font[count($font)-1]['ReqFontStyle'];
			$lastfontstyle = $font[count($font)-1]['style'];
		}
		else {
			$lastfontreqstyle=null;
			$lastfontstyle=null;
		}
		if ($blockdir == 'ltr' && strpos($lastfontreqstyle,"I") !== false && strpos($lastfontstyle,"I") === false) {	// Artificial italic
			$lastitalic = $this->FontSize*0.15*_MPDFK;
		}
		else { $lastitalic = 0; }


/*-- LISTS --*/
		if ($is_list && is_array($this->bulletarray) && count($this->bulletarray)) {
	  		$actfs = $this->bulletarray['fontsize'];
			if (!$lhfixed) { $maxlineHeight = max($maxlineHeight,$actfs * $this->lineheight_correction );  }
			if ($lhfixed && $actfs > $def_fontsize) { $this->forceExactLineheight = false; }
			$maxfontsize = max($maxfontsize,$actfs);
		}
/*-- END LISTS --*/

		// when every text item checked i.e. $maxfontsize is set properly

		$af = 0; 	// Above font
		$bf = 0; 	// Below font
		$mta = 0;	// Maximum top-aligned 
		$mba = 0;	// Maximum bottom-aligned 

		foreach ( $content as $k => $chunk )
		{
		  if (isset($this->objectbuffer[$k])) { 
			$oh = $this->objectbuffer[$k]['OUTER-HEIGHT'];
			$va = $this->objectbuffer[$k]['vertical-align']; // = $objattr['vertical-align'] = set as M,T,B,S
			if ($lhfixed && $oh > $def_fontsize) { $this->forceExactLineheight = false; }

			if ($va == 'BS') {	//  (BASELINE default)
				$af = max($af, ($oh - ($maxfontsize * (0.5 + $this->baselineC))));
			}
			else if ($va == 'M') { 
				$af = max($af, ($oh - $maxfontsize)/2);
				$bf = max($bf, ($oh - $maxfontsize)/2);
			}
			else if ($va == 'TT') { 
				$bf = max($bf, ($oh - $maxfontsize));
			}
			else if ($va == 'TB') { 
				$af = max($af, ($oh - $maxfontsize));
			}
			else if ($va == 'T') { 
				$mta = max($mta, $oh);
			}
			else if ($va == 'B') { 
				$mba = max($mba, $oh);
			}
		  }
		}
		if ((!$lhfixed || !$this->forceExactLineheight) && ($af > (($maxlineHeight - $maxfontsize)/2) || $bf > (($maxlineHeight - $maxfontsize)/2))) {
			$maxlineHeight = $maxfontsize + $af + $bf;
		}
		else if (!$lhfixed) { $af = $bf = ($maxlineHeight - $maxfontsize)/2; }
		if ($mta > $maxlineHeight) { 
			$bf += ($mta - $maxlineHeight);
			$maxlineHeight = $mta;
		}
		if ($mba > $maxlineHeight) { 
			$af += ($mba - $maxlineHeight);
			$maxlineHeight = $mba;
		}

		$lineHeight = $maxlineHeight;
		// If NOT images, and maxfontsize NOT > lineHeight - this value determines text baseline positioning
		if ($lhfixed && $af==0 && $bf==0 && $maxfontsize<=($def_fontsize * $this->lineheight_correction * 0.8 )) { 
			$this->linemaxfontsize = $def_fontsize; 
		}
		else { $this->linemaxfontsize = $maxfontsize; }

		// Get PAGEBREAK TO TEST for height including the bottom border/padding
		$check_h = max($this->divheight,$lineHeight);

		// This fixes a proven bug...
		if ($endofblock && $newblock && $blockstate==0 && !$content) {  $check_h = 0; }
		// but ? needs to fix potentially more widespread...
	//	if (!$content) {  $check_h = 0; }

		if ($this->blklvl > 0 && !$is_table) { 
		   if ($endofblock && $blockstate > 1) { 
			if ($this->blk[$this->blklvl]['page_break_after_avoid']) {  $check_h += $lineHeight; }
			$check_h += ($this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['border_bottom']['w']);
		   }
		   //	mPDF 5.4.03
		   if (($newblock && ($blockstate==1 || $blockstate==3) && $lineCount == 0) || ($endofblock && $blockstate ==3 && $lineCount == 0)) { 
			$check_h += ($this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['border_top']['w']);
		   }
		}

		// Force PAGE break if column height cannot take check-height
		if ($this->ColActive && $check_h > ($this->PageBreakTrigger - $this->y0)) { 
			$this->SetCol($this->NbCol-1);
		}
 
		//	mPDF 5.4.04
		// Avoid just border/background-color moved on to next page
		if ($endofblock && $blockstate > 1 && !$content) { $buff = $this->margBuffer; }
		else { $buff = 0; }


		// PAGEBREAK
		// mPDF 5.4.04
		if(!$is_table && ($this->y+$check_h) > ($this->PageBreakTrigger + $buff) and !$this->InFooter and $this->AcceptPageBreak()) {
  	     		$bak_x=$this->x;//Current X position
			// WORD SPACING
			$ws=$this->ws;//Word Spacing
			$charspacing=$this->charspacing;//Character Spacing
			$this->ResetSpacing();

		      $this->AddPage($this->CurOrientation);

		      $this->x=$bak_x;
			// Added to correct for OddEven Margins
			$currentx += $this->MarginCorrection;
			$this->x += $this->MarginCorrection;

			// WORD SPACING
			$this->SetSpacing($charspacing,$ws);
		}

		if ($this->keep_block_together && !$is_table && $this->kt_p00 < $this->page && ($this->y+$check_h) > $this->kt_y00) {
			$this->printdivbuffer();
			$this->keep_block_together = 0;
		}

/*-- COLUMNS --*/
		// COLS
		// COLUMN CHANGE
		if ($this->CurrCol != $oldcolumn) {
			$currentx += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);
			$this->x += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);
			$oldcolumn = $this->CurrCol;
		}


		if ($this->ColActive && !$is_table) { $this->breakpoints[$this->CurrCol][] = $this->y; }
/*-- END COLUMNS --*/

		// TOP MARGIN
		if ($newblock && ($blockstate==1 || $blockstate==3) && ($this->blk[$this->blklvl]['margin_top']) && $lineCount == 0 && !$is_table && !$is_list) { 
			$this->DivLn($this->blk[$this->blklvl]['margin_top'],$this->blklvl-1,true,$this->blk[$this->blklvl]['margin_collapse']); 
			if ($this->ColActive) { $this->breakpoints[$this->CurrCol][] = $this->y; }	// *COLUMNS*
		}

		if ($newblock && ($blockstate==1 || $blockstate==3) && $lineCount == 0 && !$is_table && !$is_list) { 
			$this->blk[$this->blklvl]['y0'] = $this->y;
			$this->blk[$this->blklvl]['startpage'] = $this->page;
			if ($this->blk[$this->blklvl]['float']) { $this->blk[$this->blklvl]['float_start_y'] = $this->y; } // mPDF 5.6.63
			if ($this->ColActive) { $this->breakpoints[$this->CurrCol][] = $this->y; }	// *COLUMNS*
		}

	// ADDED for Paragraph_indent
	$WidthCorrection = 0;
	if (($newblock) && ($blockstate==1 || $blockstate==3) && isset($this->blk[$this->blklvl]['text_indent']) && ($lineCount == 0) && (!$is_table) && (!$is_list) && ($align != 'C')) { 
		$ti = $this->ConvertSize($this->blk[$this->blklvl]['text_indent'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); 
		$WidthCorrection = ($ti*_MPDFK); 
	} 


	// PADDING and BORDER spacing/fill
	if (($newblock) && ($blockstate==1 || $blockstate==3) && (($this->blk[$this->blklvl]['padding_top']) || ($this->blk[$this->blklvl]['border_top'])) && ($lineCount == 0) && (!$is_table) && (!$is_list)) { 
			// $state = 0 normal; 1 top; 2 bottom; 3 top and bottom
			$this->DivLn($this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'],-3,true,false,1); 
			if ($this->ColActive) { $this->breakpoints[$this->CurrCol][] = $this->y; }	// *COLUMNS*
			$this->x = $currentx;
	}


	// Added mPDF 3.0 Float DIV
	$fpaddingR = 0;
	$fpaddingL = 0;
/*-- CSS-FLOAT --*/
	if (count($this->floatDivs)) {
		list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl);
		if ($r_exists) { $fpaddingR = $r_width; }
		if ($l_exists) { $fpaddingL = $l_width; }
	}
/*-- END CSS-FLOAT --*/

	$usey = $this->y + 0.002;
	if (($newblock) && ($blockstate==1 || $blockstate==3) && ($lineCount == 0) ) { 
		$usey += $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'];
	}
/*-- CSS-IMAGE-FLOAT --*/
	// If float exists at this level
	if (isset($this->floatmargins['R']) && $usey <= $this->floatmargins['R']['y1'] && $usey >= $this->floatmargins['R']['y0'] && !$this->floatmargins['R']['skipline']) { $fpaddingR += $this->floatmargins['R']['w']; }
	if (isset($this->floatmargins['L']) && $usey <= $this->floatmargins['L']['y1'] && $usey >= $this->floatmargins['L']['y0'] && !$this->floatmargins['L']['skipline']) { $fpaddingL += $this->floatmargins['L']['w']; }
/*-- END CSS-IMAGE-FLOAT --*/

	if ($content) {

		// In FinishFlowing Block no lines are justified as it is always last line
		// but if CJKorphan has allowed content width to go over max width, use J charspacing to compress line
		// JUSTIFICATION J - NOT!
		$nb_carac = 0;
		$nb_spaces = 0;
		$jcharspacing = 0;
		$jws = 0;
		$inclCursive=false;
		$dottab = false; // mPDF 5.6.19
		foreach ( $content as $k => $chunk ) {
			if (!isset($this->objectbuffer[$k]) || (isset($this->objectbuffer[$k]) && !$this->objectbuffer[$k])) {
				if ($this->usingCoreFont) {
				      $chunk = str_replace(chr(160),chr(32),$chunk );
				}
				else {
				      $chunk = str_replace(chr(194).chr(160),chr(32),$chunk ); 
				}
				$nb_carac += mb_strlen( $chunk, $this->mb_enc );  
				$nb_spaces += mb_substr_count( $chunk,' ', $this->mb_enc );  
				if ($checkCursive) {
					if (preg_match("/([".$this->pregRTLchars."])/u", $chunk)) { $inclCursive = true; }	// *RTL*
					if (preg_match("/([".$this->pregHIchars.$this->pregBNchars.$this->pregPAchars."])/u", $chunk)) { $inclCursive = true; }	// *INDIC*
				}
			}
			else if ($this->objectbuffer[$k]['type']=='dottab') { $dottab = $this->objectbuffer[$k]['outdent']; } // mPDF 5.6.19
		}
		// if it's justified, we need to find the char/word spacing (or if orphans have allowed length of line to go over the maxwidth)
		// If "orphans" in fact is just a final space - ignore this
		// mPDF 5.6.40
		$lastchar = mb_substr($content[(count($content)-1)],mb_strlen($content[(count($content)-1)], $this->mb_enc)-1, 1, $this->mb_enc);
		if (preg_match("/[".$this->CJKoverflow."]/u", $lastchar)) { $CJKoverflow = true; }
		else {$CJKoverflow = false; } 
		if ((((($contentWidth + $lastitalic) > $maxWidth) && ($content[count($content)-1] != ' ') )  ||
			(!$endofblock && $align=='J' && ($next=='image' || $next=='select' || $next=='input' || $next=='textarea' || ($next=='br' && $this->justifyB4br))))  && !($CJKoverflow && $this->allowCJKoverflow) ) {	// mPDF 5.6.40
 		  // WORD SPACING
			list($jcharspacing,$jws) = $this->GetJspacing($nb_carac,$nb_spaces,($maxWidth-$lastitalic-$contentWidth-$WidthCorrection-(($this->cMarginL+$this->cMarginR)*_MPDFK)-($paddingL+$paddingR +(($fpaddingL + $fpaddingR) * _MPDFK) )),$inclCursive);
		}
/*-- CJK-FONTS --*/
		// mPDF 5.6.40
		else if ($this->checkCJK && $align=='J' && $CJKoverflow && $this->allowCJKoverflow && $this->CJKforceend) {
		// force-end overhang
			$hanger = mb_substr($content[count($content)-1],mb_strlen($content[count($content)-1],$this->mb_enc)-1,1,$this->mb_enc );
			if (preg_match("/[".$this->CJKoverflow."]/u", $hanger)) {
				$content[count($content)-1] = mb_substr($content[count($content)-1],0,mb_strlen($content[count($content)-1],$this->mb_enc)-1,$this->mb_enc );
				$this->restoreFont( $font[ count($content)-1 ],false );
				$contentWidth -= $this->GetStringWidth($hanger) * _MPDFK;
				$nb_carac -= 1;
				list($jcharspacing,$jws) = $this->GetJspacing($nb_carac,$nb_spaces,($maxWidth-$lastitalic-$contentWidth-$WidthCorrection-(($this->cMarginL+$this->cMarginR)*_MPDFK)-($paddingL+$paddingR +(($fpaddingL + $fpaddingR) * _MPDFK) )),$inclCursive);
			}
		}
/*-- END CJK-FONTS --*/

		// Check if will fit at word/char spacing of previous line - if so continue it
		// but only allow a maximum of $this->jSmaxWordLast and $this->jSmaxCharLast
		else if ($contentWidth < ($maxWidth - $lastitalic-$WidthCorrection - (($this->cMarginL+$this->cMarginR)* _MPDFK) - ($paddingL+$paddingR +(($fpaddingL + $fpaddingR) * _MPDFK))) && !$this->fixedlSpacing) {
			if ($this->ws > $this->jSmaxWordLast) {
				$jws = $this->jSmaxWordLast;
			}
			if ($this->charspacing > $this->jSmaxCharLast) {
				$jcharspacing = $this->jSmaxCharLast;
			}
			$check = $maxWidth - $lastitalic-$WidthCorrection - $contentWidth - (($this->cMarginL+$this->cMarginR)* _MPDFK) - ($paddingL+$paddingR +(($fpaddingL + $fpaddingR) * _MPDFK) ) - ( $jcharspacing * $nb_carac) - ( $jws * $nb_spaces);
			if ($check <= 0) {
				$jcharspacing = 0;
				$jws = 0;
			}
		}

		$empty = $maxWidth - $lastitalic-$WidthCorrection - $contentWidth - (($this->cMarginL+$this->cMarginR)* _MPDFK) - ($paddingL+$paddingR +(($fpaddingL + $fpaddingR) * _MPDFK) );

		$empty -= ($jcharspacing * $nb_carac);
		$empty -= ($jws * $nb_spaces);

		$empty /= _MPDFK;

		if (!$is_table) { 
			$this->maxPosR = max($this->maxPosR , ($this->w - $this->rMargin - $this->blk[$this->blklvl]['outer_right_margin'] - $empty)); 
			$this->maxPosL = min($this->maxPosL , ($this->lMargin + $this->blk[$this->blklvl]['outer_left_margin'] + $empty)); 
		}

		$arraysize = count($content);

		$margins = ($this->cMarginL+$this->cMarginR) + ($ipaddingL+$ipaddingR + $fpaddingR + $fpaddingR );

		if (!$is_table) { $this->DivLn($lineHeight,$this->blklvl,false); }	// false -> don't advance y

		// DIRECTIONALITY RTL
		$all_rtl = false;
		$contains_rtl = false;
/*-- RTL --*/
   		if ($blockdir == 'rtl' || $this->biDirectional)  {
			$all_rtl = true;
			foreach ( $content as $k => $chunk ) {
				$reversed = $this->magic_reverse_dir($chunk, false, $blockdir);
				if ($reversed > 0) { $contains_rtl = true; }
				if ($reversed < 2) { $all_rtl = false; }
				$content[$k] = $chunk;
			}
			if (($blockdir =='rtl' && $contains_rtl) || $all_rtl) { 
				$content = array_reverse($content,false); 
				$contentB = array_reverse($contentB,false);
			}
		}
/*-- END RTL --*/

		$this->x = $currentx + $this->cMarginL + $ipaddingL + $fpaddingL;
		if ($dottab !== false && $blockdir=='rtl') { $this->x -= $dottab; } // mPDF 5.6.19
		else if ($align == 'R') { $this->x += $empty; }
		else if ($align == 'J' && $blockdir == 'rtl') { $this->x += $empty; }
		else if ($align == 'C') { $this->x += ($empty / 2); }

		// Paragraph INDENT
		$WidthCorrection = 0; 
		if (($newblock) && ($blockstate==1 || $blockstate==3) && isset($this->blk[$this->blklvl]['text_indent']) && ($lineCount == 0) && (!$is_table) && (!$is_list) && ($align !='C')) { 
			$ti = $this->ConvertSize($this->blk[$this->blklvl]['text_indent'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); 
			$this->x += $ti; 
		}


          foreach ( $content as $k => $chunk )
          {

		// FOR IMAGES
		if ((($blockdir == 'rtl') && ($contains_rtl )) || $all_rtl ) { $dirk = $arraysize-1 - $k; } else { $dirk = $k; }

		$va = 'M';	// default for text
		if (isset($this->objectbuffer[$dirk]) && $this->objectbuffer[$dirk]) {
			$xadj = $this->x - $this->objectbuffer[$dirk]['OUTER-X']; 
			$this->objectbuffer[$dirk]['OUTER-X'] += $xadj;
			$this->objectbuffer[$dirk]['BORDER-X'] += $xadj;
			$this->objectbuffer[$dirk]['INNER-X'] += $xadj;
			$va = $this->objectbuffer[$dirk]['vertical-align'];
			$yadj = $this->y - $this->objectbuffer[$dirk]['OUTER-Y'];
			if ($va == 'BS') { 
				$yadj += $af + ($this->linemaxfontsize * (0.5 + $this->baselineC)) - $this->objectbuffer[$dirk]['OUTER-HEIGHT'];
			}
			else if ($va == 'M' || $va == '') { 
				$yadj += $af + ($this->linemaxfontsize /2) - ($this->objectbuffer[$dirk]['OUTER-HEIGHT']/2);
			}
			else if ($va == 'TB') { 
				$yadj += $af + $this->linemaxfontsize - $this->objectbuffer[$dirk]['OUTER-HEIGHT'];
			}
			else if ($va == 'TT') { 
				$yadj += $af;
			}
			else if ($va == 'B') { 
				$yadj += $af + $this->linemaxfontsize + $bf - $this->objectbuffer[$dirk]['OUTER-HEIGHT'];
			}
			else if ($va == 'T') { 
				$yadj += 0;
			}
			$this->objectbuffer[$dirk]['OUTER-Y'] += $yadj;
			$this->objectbuffer[$dirk]['BORDER-Y'] += $yadj;
			$this->objectbuffer[$dirk]['INNER-Y'] += $yadj;
		}


			// DIRECTIONALITY RTL
			if ((($blockdir == 'rtl') && ($contains_rtl )) || $all_rtl ) { $this->restoreFont( $font[ $arraysize-1 - $k ] ); }
			else { $this->restoreFont( $font[ $k ] ); }

			// mPDF 5.6.13 Decimal alignment - set in _tableWrite
			if ($is_table && substr($align,0,1)=='D' && $k==0 ) {
				$dp = $this->decimal_align[substr($align,0,2)];
				$s = preg_split('/'.preg_quote($dp,'/').'/', $content[0], 2); 	// ? needs to be /u if not core
				$s0 = $this->GetStringWidth($s[0], false);
				$this->x += ($this->decimal_offset - $s0);
			}

			$this->SetSpacing(($this->fixedlSpacing*_MPDFK)+$jcharspacing,($this->fixedlSpacing+$this->minwSpacing)*_MPDFK+$jws);
			$this->fixedlSpacing = false;
			$this->minwSpacing = 0;

			// mPDF 5.6.26
			$save_vis = $this->visibility;
			if (isset($this->textparam['visibility']) && $this->textparam['visibility'] && $this->textparam['visibility'] != $this->visibility) {
				$this->SetVisibility($this->textparam['visibility']);
			}

	 		// *********** SPAN BACKGROUND COLOR ***************** //
			if (isset($this->spanbgcolor) && $this->spanbgcolor) { 
				$cor = $this->spanbgcolorarray;
				$this->SetFColor($cor);
				$save_fill = $fill; $spanfill = 1; $fill = 1;
			}
			if (!empty($this->spanborddet)) { 
				if (strpos($contentB[$k],'L')!==false && isset($this->spanborddet['L'])) $this->x += $this->spanborddet['L']['w'];
				if (strpos($contentB[$k],'L')===false) $this->spanborddet['L']['s'] = $this->spanborddet['L']['w'] = 0; 
				if (strpos($contentB[$k],'R')===false) $this->spanborddet['R']['s'] = $this->spanborddet['R']['w'] = 0; 
			}
			// WORD SPACING
		      $stringWidth = $this->GetStringWidth($chunk ) + ( $this->charspacing * mb_strlen($chunk,$this->mb_enc ) / _MPDFK )  
				+ ( $this->ws * mb_substr_count($chunk,' ',$this->mb_enc ) / _MPDFK );
			if (isset($this->objectbuffer[$dirk])) { 
				if ($this->objectbuffer[$dirk]['type']=='dottab') { 
					$this->objectbuffer[$dirk]['OUTER-WIDTH'] +=$empty; 
					$this->objectbuffer[$dirk]['OUTER-WIDTH'] +=$this->objectbuffer[$dirk]['outdent']; // mPDF 5.6.19
				}
				$stringWidth = $this->objectbuffer[$dirk]['OUTER-WIDTH'];
			}

			if ($stringWidth==0) { $stringWidth = 0.000001; }
			if ($k == $arraysize-1) {
				// mPDF 5.6.40
				if ($this->checkCJK && $CJKoverflow && $align=='J' && $this->allowCJKoverflow && $hanger && $this->CJKforceend) {
				  // force-end overhang
					$this->Cell( $stringWidth, $lineHeight, $chunk, '', 0, '', $fill, $this->HREF, $currentx,0,0,'M', $fill, $af, $bf, true ); 
					$this->Cell( $this->GetStringWidth($hanger), $lineHeight, $hanger, '', 1, '', $fill, $this->HREF, $currentx,0,0,'M', $fill, $af, $bf, true );
				}
				else {
					$this->Cell( $stringWidth, $lineHeight, $chunk, '', 1, '', $fill, $this->HREF, $currentx,0,0,'M', $fill, $af, $bf, true );
				}
			}
			else $this->Cell( $stringWidth, $lineHeight, $chunk, '', 0, '', $fill, $this->HREF, 0, 0,0,'M', $fill, $af, $bf, true );//first or middle part


			if (!empty($this->spanborddet)) { 
				if (strpos($contentB[$k],'R')!==false && $k != $arraysize-1)  $this->x += $this->spanborddet['R']['w'];
			}
	 		// *********** SPAN BACKGROUND COLOR OFF - RESET BLOCK BGCOLOR ***************** //
			if (isset($spanfill) && $spanfill) { 
				$fill = $save_fill; $spanfill = 0; 
				if ($fill) { $this->SetFColor($bcor); }
			}
			// mPDF 5.6.26
			if (isset($this->textparam['visibility']) && $this->textparam['visibility'] && $this->visibility != $save_vis) {
				$this->SetVisibility($save_vis);
			}

          }

	$this->printobjectbuffer($is_table, $blockdir);

	$this->objectbuffer = array();

	$this->ResetSpacing();

/*-- LISTS --*/
	// LIST BULLETS/NUMBERS
	if ($is_list && is_array($this->bulletarray) && ($lineCount == 0) ) {
	  
	  $savedFont = $this->saveFont();

	  $bull = $this->bulletarray;
	  if (isset($bull['level']) && isset($bull['occur']) && isset($this->InlineProperties['LIST'][$bull['level']][$bull['occur']])) { 
		$this->restoreInlineProperties($this->InlineProperties['LIST'][$bull['level']][$bull['occur']]); 
	  }
	  if (isset($bull['level']) && isset($bull['occur']) && isset($bull['num']) && isset($this->InlineProperties['LISTITEM'][$bull['level']][$bull['occur']][$bull['num']]) && $this->InlineProperties['LISTITEM'][$bull['level']][$bull['occur']][$bull['num']]) { $this->restoreInlineProperties($this->InlineProperties['LISTITEM'][$bull['level']][$bull['occur']][$bull['num']]); }
	  if (isset($bull['font']) && $bull['font'] == 'czapfdingbats') {
		$this->bullet = true;
		$this->SetFont('czapfdingbats','',$this->FontSizePt/2.5);
	  }
	  else { $this->SetFont($this->FontFamily,$this->FontStyle,$this->FontSizePt,true,true); }	// force output
       //Output bullet
	  $this->x = $currentx;
	  if (isset($bull['x'])) { $this->x += $bull['x']; }
	  $this->y -= $lineHeight;
	  if (isset($bull['col']) && $bull['col']) { $this->SetTColor($bull['col']); }	// mPDF 5.6.67

        if (isset($bull['txt'])) { $this->Cell($bull['w'], $lineHeight,$bull['txt'],'','',$bull['align'],0,'',0,-$this->cMarginL, -$this->cMarginR ); }
	  if (isset($bull['font']) && $bull['font'] == 'czapfdingbats') {
		$this->bullet = false;
	  }
	  $this->x = $currentx;	// Reset
	  $this->y += $lineHeight;

	  if ($this->ColActive && !$is_table) { $this->breakpoints[$this->CurrCol][] = $this->y; }	// *COLUMNS*

	   $this->restoreFont( $savedFont );
	  // $font = array( $savedFont );

	  $this->bulletarray = array();	// prevents repeat of bullet/number if <li>....<br />.....</li>
	}
/*-- END LISTS --*/


	}	// END IF CONTENT

/*-- CSS-IMAGE-FLOAT --*/
	// Update values if set to skipline
	if ($this->floatmargins) { $this->_advanceFloatMargins(); }


	if ($endofblock && $blockstate>1) { 
		// If float exists at this level
		if (isset($this->floatmargins['R']['y1'])) { $fry1 = $this->floatmargins['R']['y1']; }
		else { $fry1 = 0; }
		if (isset($this->floatmargins['L']['y1'])) { $fly1 = $this->floatmargins['L']['y1']; }
		else { $fly1 = 0; }
		if ($this->y < $fry1 || $this->y < $fly1) { 
			$drop = max($fry1,$fly1) - $this->y;
			$this->DivLn($drop); 
			$this->x = $currentx;
		}
	}
/*-- END CSS-IMAGE-FLOAT --*/


	// PADDING and BORDER spacing/fill
	if ($endofblock && ($blockstate > 1) && ($this->blk[$this->blklvl]['padding_bottom'] || $this->blk[$this->blklvl]['border_bottom'] || $this->blk[$this->blklvl]['css_set_height']) && (!$is_table) && (!$is_list)) { 
			// If CSS height set, extend bottom - if on same page as block started, and CSS HEIGHT > actual height, 
			//	and does not force pagebreak
			$extra = 0;
			if ($this->blk[$this->blklvl]['css_set_height'] && $this->blk[$this->blklvl]['startpage']==$this->page) {
				// predicted height
				$h1 = ($this->y-$this->blk[$this->blklvl]['y0']) + $this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['border_bottom']['w'];
				if ($h1 < ($this->blk[$this->blklvl]['css_set_height']+$this->blk[$this->blklvl]['padding_bottom']+$this->blk[$this->blklvl]['padding_top'])) { $extra = ($this->blk[$this->blklvl]['css_set_height']+$this->blk[$this->blklvl]['padding_bottom']+$this->blk[$this->blklvl]['padding_top']) - $h1; }
				if($this->y + $this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['border_bottom']['w'] + $extra > $this->PageBreakTrigger) {
					$extra = $this->PageBreakTrigger - ($this->y + $this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['border_bottom']['w']); 
				}
			}

			// $state = 0 normal; 1 top; 2 bottom; 3 top and bottom
			$this->DivLn($this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['border_bottom']['w'] + $extra,-3,true,false,2); 
			$this->x = $currentx;

			if ($this->ColActive) { $this->breakpoints[$this->CurrCol][] = $this->y; }	// *COLUMNS*

	}

	// SET Bottom y1 of block (used for painting borders)
	if (($endofblock) && ($blockstate > 1) && (!$is_table) && (!$is_list)) { 
		$this->blk[$this->blklvl]['y1'] = $this->y;
	}

	// BOTTOM MARGIN
	if (($endofblock) && ($blockstate > 1) && ($this->blk[$this->blklvl]['margin_bottom']) && (!$is_table) && (!$is_list)) { 
		if($this->y+$this->blk[$this->blklvl]['margin_bottom'] < $this->PageBreakTrigger and !$this->InFooter) {
		  $this->DivLn($this->blk[$this->blklvl]['margin_bottom'],$this->blklvl-1,true,$this->blk[$this->blklvl]['margin_collapse']); 
		  if ($this->ColActive) { $this->breakpoints[$this->CurrCol][] = $this->y; }	// *COLUMNS*
		}
	}

	// Reset lineheight
	$lineHeight = $this->divheight;
}





function printobjectbuffer($is_table=false, $blockdir=false) {
		if (!$blockdir) { $blockdir = $this->directionality; }
		if ($is_table && $this->shrin_k > 1) { $k = $this->shrin_k; } 
		else { $k = 1; }
		$save_y = $this->y;
		$save_x = $this->x;
		$save_currentfontfamily = $this->FontFamily;
		$save_currentfontsize = $this->FontSizePt;
		$save_currentfontstyle = $this->FontStyle.($this->U ? 'U' : '').($this->S ? 'S' : '');
		if ($blockdir == 'rtl') { $rtlalign = 'R'; } else { $rtlalign = 'L'; }
		foreach ($this->objectbuffer AS $ib => $objattr) { 
		   if ($objattr['type'] == 'bookmark' || $objattr['type'] == 'indexentry' || $objattr['type'] == 'toc') {
			$x = $objattr['OUTER-X']; 
			$y = $objattr['OUTER-Y'];
			$this->y = $y - $this->FontSize/2;
			$this->x = $x;
			if ($objattr['type'] == 'bookmark' ) { $this->Bookmark($objattr['CONTENT'],$objattr['bklevel'] ,$y - $this->FontSize); }	// *BOOKMARKS*
			if ($objattr['type'] == 'indexentry') { $this->IndexEntry($objattr['CONTENT']); }	// *INDEX*
			if ($objattr['type'] == 'toc') { $this->TOC_Entry($objattr['CONTENT'], $objattr['toclevel'], $objattr['toc_id']); }	// *TOC*
		   }
/*-- ANNOTATIONS --*/
		   else if ($objattr['type'] == 'annot') {
			if ($objattr['POS-X']) { $x = $objattr['POS-X']; }
			else if ($this->annotMargin<>0) { $x = -$objattr['OUTER-X']; }
			else { $x = $objattr['OUTER-X']; }
			if ($objattr['POS-Y']) { $y = $objattr['POS-Y']; }
			else { $y = $objattr['OUTER-Y'] - $this->FontSize/2; }
			// Create a dummy entry in the _out/columnBuffer with position sensitive data,
			// linking $y-1 in the Columnbuffer with entry in $this->columnAnnots
			// and when columns are split in length will not break annotation from current line
			$this->y = $y-1;
			$this->x = $x-1;
			$this->Line($x-1,$y-1,$x-1,$y-1);
			$this->Annotation($objattr['CONTENT'], $x , $y , $objattr['ICON'], $objattr['AUTHOR'], $objattr['SUBJECT'], $objattr['OPACITY'], $objattr['COLOR'], $objattr['POPUP'], $objattr['FILE']);
		   }
/*-- END ANNOTATIONS --*/
		   else { 
			$y = $objattr['OUTER-Y'];
			$x = $objattr['OUTER-X'];
			$w = $objattr['OUTER-WIDTH'];
			$h = $objattr['OUTER-HEIGHT'];
			if (isset($objattr['text'])) { $texto = $objattr['text']; }
			$this->y = $y;
			$this->x = $x;
			if (isset($objattr['fontfamily'])) { $this->SetFont($objattr['fontfamily'],'',$objattr['fontsize'] ); }
		   }

		// HR
		   if ($objattr['type'] == 'hr') {
			$this->SetDColor($objattr['color']);
			switch($objattr['align']) {
      		    case 'C':
      		        $empty = $objattr['OUTER-WIDTH'] - $objattr['INNER-WIDTH'];
      		        $empty /= 2;
      		        $x += $empty;
     		        	  break;
      		    case 'R':
      		        $empty = $objattr['OUTER-WIDTH'] - $objattr['INNER-WIDTH'];
      		        $x += $empty;
      		        break;
			}
      		$oldlinewidth = $this->LineWidth;
			$this->SetLineWidth($objattr['linewidth']/$k );
			$this->y += ($objattr['linewidth']/2) + $objattr['margin_top']/$k;
			$this->Line($x,$this->y,$x+$objattr['INNER-WIDTH'],$this->y);
			$this->SetLineWidth($oldlinewidth);
			$this->SetDColor($this->ConvertColor(0));
		   }
		// IMAGE
		   if ($objattr['type'] == 'image') {
			// mPDF 5.6.01  - LAYERS
			if (isset($objattr['z-index']) && $objattr['z-index'] > 0 && $this->currentlayer==0) {
				$this->BeginLayer($objattr['z-index']);
			}
			if(isset($objattr['visibility']) && $objattr['visibility']!='visible' && $objattr['visibility']) {
				$this->SetVisibility($objattr['visibility']);
			}
			if (isset($objattr['opacity'])) { $this->SetAlpha($objattr['opacity']); }
			$rotate = 0;
			$obiw = $objattr['INNER-WIDTH'];
			$obih = $objattr['INNER-HEIGHT'];
			$sx = $objattr['INNER-WIDTH']*_MPDFK / $objattr['orig_w'];
			$sy = abs($objattr['INNER-HEIGHT'])*_MPDFK / abs($objattr['orig_h']);
			$sx = ($objattr['INNER-WIDTH']*_MPDFK / $objattr['orig_w']);
			$sy = ($objattr['INNER-HEIGHT']*_MPDFK / $objattr['orig_h']);

			if (isset($objattr['ROTATE'])) { $rotate = $objattr['ROTATE']; }
			if ($rotate==90) { 
				// Clockwise
				$obiw = $objattr['INNER-HEIGHT'];
				$obih = $objattr['INNER-WIDTH'];
				$tr = $this->transformTranslate(0, -$objattr['INNER-WIDTH'], true) ;
				$tr .= ' '. $this->transformRotate(90, $objattr['INNER-X'],($objattr['INNER-Y'] +$objattr['INNER-WIDTH'] ),true) ;
				$sx = $obiw*_MPDFK / $objattr['orig_h'];
				$sy = $obih*_MPDFK / $objattr['orig_w'];
			}
			else if ($rotate==-90 || $rotate==270) { 
				// AntiClockwise
				$obiw = $objattr['INNER-HEIGHT'];
				$obih = $objattr['INNER-WIDTH'];
				$tr = $this->transformTranslate($objattr['INNER-WIDTH'], ($objattr['INNER-HEIGHT']-$objattr['INNER-WIDTH']), true) ;
				$tr .= ' '. $this->transformRotate(-90, $objattr['INNER-X'],($objattr['INNER-Y'] +$objattr['INNER-WIDTH'] ),true) ;
				$sx = $obiw*_MPDFK / $objattr['orig_h'];
				$sy = $obih*_MPDFK / $objattr['orig_w'];
			}
			else if ($rotate==180) { 
				// Mirror
				$tr = $this->transformTranslate($objattr['INNER-WIDTH'], -$objattr['INNER-HEIGHT'], true) ;
				$tr .= ' '. $this->transformRotate(180, $objattr['INNER-X'],($objattr['INNER-Y'] +$objattr['INNER-HEIGHT'] ),true) ;
			}
			else { $tr = ''; }
			$tr = trim($tr);
			if ($tr) { $tr .= ' '; }
			$gradmask = '';


/*-- BACKGROUNDS --*/
			if (isset($objattr['GRADIENT-MASK'])) { 
				$g = $this->grad->parseMozGradient( $objattr['GRADIENT-MASK'] );
				if ($g) {
					$dummy = $this->grad->Gradient($objattr['INNER-X'], $objattr['INNER-Y'], $obiw, $obih, $g['type'], $g['stops'], $g['colorspace'], $g['coords'], $g['extend'], true, true);
					$gradmask = '/TGS'.count($this->gradients).' gs ';
					// $this->_out("q ".$tr.$this->grad->Gradient($objattr['INNER-X'], $objattr['INNER-Y'], $obiw, $obih, $g['type'], $g['stops'], $g['colorspace'], $g['coords'], $g['extend'], true)." Q");
				}
			}
/*-- END BACKGROUNDS --*/
/*-- IMAGES-WMF --*/
			if (isset($objattr['itype']) && $objattr['itype']=='wmf') { 
				$outstring = sprintf('q '.$tr.'%.3F 0 0 %.3F %.3F %.3F cm /FO%d Do Q', $sx, -$sy, $objattr['INNER-X']*_MPDFK-$sx*$objattr['wmf_x'], (($this->h-$objattr['INNER-Y'])*_MPDFK)+$sy*$objattr['wmf_y'], $objattr['ID']);
			}
			else  
/*-- END IMAGES-WMF --*/
			if (isset($objattr['itype']) && $objattr['itype']=='svg') { 
				$outstring = sprintf('q '.$tr.'%.3F 0 0 %.3F %.3F %.3F cm /FO%d Do Q', $sx, -$sy, $objattr['INNER-X']*_MPDFK-$sx*$objattr['wmf_x'], (($this->h-$objattr['INNER-Y'])*_MPDFK)+$sy*$objattr['wmf_y'], $objattr['ID']);
			}
			else { 
				$outstring = sprintf("q ".$tr."%.3F 0 0 %.3F %.3F %.3F cm ".$gradmask."/I%d Do Q",$obiw*_MPDFK, $obih*_MPDFK, $objattr['INNER-X'] *_MPDFK, ($this->h-($objattr['INNER-Y'] +$obih ))*_MPDFK,$objattr['ID'] );
			}
			$this->_out($outstring);
			// LINK
			if (isset($objattr['link'])) $this->Link($objattr['INNER-X'],$objattr['INNER-Y'],$objattr['INNER-WIDTH'],$objattr['INNER-HEIGHT'],$objattr['link']);
			if (isset($objattr['opacity'])) { $this->SetAlpha(1); }
			if ((isset($objattr['border_top']) && $objattr['border_top']>0) || (isset($objattr['border_left']) && $objattr['border_left']>0) || (isset($objattr['border_right']) && $objattr['border_right']>0) || (isset($objattr['border_bottom']) && $objattr['border_bottom']>0)) { $this->PaintImgBorder($objattr,$is_table); }
			if(isset($objattr['visibility']) && $objattr['visibility']!='visible' && $objattr['visibility']) {
				$this->SetVisibility('visible');
			}
			// mPDF 5.6.01  - LAYERS
			if (isset($objattr['z-index']) && $objattr['z-index'] > 0 && $this->currentlayer==0) {
				$this->EndLayer();
			}

		   }

/*-- BARCODES --*/
		// BARCODE
		   if ($objattr['type'] == 'barcode') {
			$bgcol = $this->ConvertColor(255);
			if (isset($objattr['bgcolor']) && $objattr['bgcolor']) {
				$bgcol = $objattr['bgcolor'];
			}
			$col = $this->ConvertColor(0);
			if (isset($objattr['color']) && $objattr['color']) {
				$col = $objattr['color'];
			}
			$this->SetFColor($bgcol);
	 		$this->Rect($objattr['BORDER-X'], $objattr['BORDER-Y'], $objattr['BORDER-WIDTH'], $objattr['BORDER-HEIGHT'], 'F');
			$this->SetFColor($this->ConvertColor(255));
			if (isset($objattr['BORDER-WIDTH'])) { $this->PaintImgBorder($objattr,$is_table); }
			if ($objattr['btype'] == 'EAN13' || $objattr['btype'] == 'ISBN' || $objattr['btype'] == 'ISSN' || $objattr['btype'] == 'UPCA' || $objattr['btype'] == 'UPCE' || $objattr['btype'] == 'EAN8') {
				$this->WriteBarcode($objattr['code'], $objattr['showtext'], $objattr['INNER-X'], $objattr['INNER-Y'], $objattr['bsize'], 0, 0, 0, 0, 0, $objattr['bheight'], $bgcol, $col, $objattr['btype'], $objattr['bsupp'], $objattr['bsupp_code'], $k);
			}
			// QR-code
			else if ($objattr['btype']=='QR') {
				if (!class_exists('QRcode', false)) { 
					include(_MPDF_PATH.'qrcode/qrcode.class.php'); 
				}
				$this->qrcode = new QRcode($objattr['code'], $objattr['errorlevel']);
				$this->qrcode->displayFPDF($this, $objattr['INNER-X'], $objattr['INNER-Y'], $objattr['bsize']*25, array(255,255,255), array(0,0,0));
			}
			else {
				$this->WriteBarcode2($objattr['code'], $objattr['INNER-X'], $objattr['INNER-Y'], $objattr['bsize'], $objattr['bheight'], $bgcol, $col, $objattr['btype'], $objattr['pr_ratio'], $k);
			}
		   }
/*-- END BARCODES --*/

		// TEXT CIRCLE
		   if ($objattr['type'] == 'textcircle') {
			$bgcol = '';	// mPDF 5.5.14
			if (isset($objattr['bgcolor']) && $objattr['bgcolor']) {
				$bgcol = $objattr['bgcolor'];
			}
			$col = $this->ConvertColor(0);
			if (isset($objattr['color']) && $objattr['color']) {
				$col = $objattr['color'];
			}
			$this->SetTColor($col);
			$this->SetFColor($bgcol);
	 		if ($bgcol) $this->Rect($objattr['BORDER-X'], $objattr['BORDER-Y'], $objattr['BORDER-WIDTH'], $objattr['BORDER-HEIGHT'], 'F');	// mPDF 5.5.14
			$this->SetFColor($this->ConvertColor(255));
			if (isset($objattr['BORDER-WIDTH'])) { $this->PaintImgBorder($objattr,$is_table); }
			if (!class_exists('directw', false)) { include(_MPDF_PATH.'classes/directw.php'); }
			if (empty($this->directw)) { $this->directw = new directw($this); }
			$save_lmfs = $this->linemaxfontsize;
			$this->linemaxfontsize = 0;
			if (isset($objattr['top-text'])) {
				$this->directw->CircularText($objattr['INNER-X']+$objattr['INNER-WIDTH']/2, $objattr['INNER-Y']+$objattr['INNER-HEIGHT']/2, $objattr['r']/$k, $objattr['top-text'], 'top', $objattr['fontfamily'], $objattr['fontsize']/$k, $objattr['fontstyle'], $objattr['space-width'], $objattr['char-width'],  $objattr['divider']);		// mPDF 5.5.23
			}
			if (isset($objattr['bottom-text'])) {
				$this->directw->CircularText($objattr['INNER-X']+$objattr['INNER-WIDTH']/2, $objattr['INNER-Y']+$objattr['INNER-HEIGHT']/2, $objattr['r']/$k, $objattr['bottom-text'], 'bottom', $objattr['fontfamily'], $objattr['fontsize']/$k, $objattr['fontstyle'], $objattr['space-width'], $objattr['char-width'],  $objattr['divider']);		// mPDF 5.5.23
			}
			$this->linemaxfontsize = $save_lmfs;
		   }

		   $this->ResetSpacing();

		// DOT-TAB
		   if ($objattr['type'] == 'dottab') {
				// mPDF 5.6.19
				if (isset($objattr['fontfamily'])) { $this->SetFont($objattr['fontfamily'],'',$objattr['fontsize'] ); }
				$sp = $this->GetStringWidth(' ');
				$nb=floor(($w-2*$sp)/$this->GetStringWidth('.'));
				if ($nb>0) { $dots=' '.str_repeat('.',$nb).' '; }
				else { $dots=' '; }
				$col = $this->ConvertColor(0);
				if (isset($objattr['colorarray']) && ($objattr['colorarray'])) {	// mPDF 5.6.19
					$col = $objattr['colorarray'];
				}
				$this->SetTColor($col);
				$save_dh = $this->divheight;	// mPDF 5.6.19
				$save_sbd = $this->spanborddet;
				$save_u = $this->U;
				$save_s = $this->strike;
				$this->spanborddet = '';
				$this->divheight = 0;	// mPDF 5.6.19
				$this->U = false;
				$this->strike = false;
				$this->Cell($w,$h,$dots,0,0,'C');
				$this->spanborddet = $save_sbd;
				$this->U = $save_u;
				$this->strike = $save_s;
				$this->divheight = $save_dh;	// mPDF 5.6.19
				// mPDF 5.0
				$this->SetTColor($this->ConvertColor(0));
		   }

/*-- FORMS --*/
		// TEXT/PASSWORD INPUT
		   if ($objattr['type'] == 'input' && ($objattr['subtype'] == 'TEXT' || $objattr['subtype'] == 'PASSWORD')) {
			$this->form->print_ob_text($objattr,$w,$h,$texto,$rtlalign,$k,$blockdir);
		   }

		// TEXTAREA
		   if ($objattr['type'] == 'textarea') {
			$this->form->print_ob_textarea($objattr,$w,$h,$texto,$rtlalign,$k,$blockdir);
		   }

		// SELECT
		   if ($objattr['type'] == 'select') {
			$this->form->print_ob_select($objattr,$w,$h,$texto,$rtlalign,$k,$blockdir);
		   }


		// INPUT/BUTTON as IMAGE
		   if ($objattr['type'] == 'input' && $objattr['subtype'] == 'IMAGE') {
			$this->form->print_ob_imageinput($objattr,$w,$h,$texto,$rtlalign,$k,$blockdir);
		   }

		// BUTTON
		   if ($objattr['type'] == 'input' && ($objattr['subtype'] == 'SUBMIT' || $objattr['subtype'] == 'RESET' || $objattr['subtype'] == 'BUTTON')) {
			$this->form->print_ob_button($objattr,$w,$h,$texto,$rtlalign,$k,$blockdir);
		   }

		// CHECKBOX
		   if ($objattr['type'] == 'input' && ($objattr['subtype'] == 'CHECKBOX')) {
			$this->form->print_ob_checkbox($objattr,$w,$h,$texto,$rtlalign,$k,$blockdir,$x,$y);
		   }
		// RADIO
		   if ($objattr['type'] == 'input' && ($objattr['subtype'] == 'RADIO')) {
			$this->form->print_ob_radio($objattr,$w,$h,$texto,$rtlalign,$k,$blockdir,$x,$y);
		   }
/*-- END FORMS --*/
		}
		$this->SetFont($save_currentfontfamily,$save_currentfontstyle,$save_currentfontsize);
		$this->y = $save_y;
		$this->x = $save_x;
		unset($content);
}


function WriteFlowingBlock( $s)
{
	$currentx = $this->x; 
	$is_table = $this->flowingBlockAttr[ 'is_table' ];
	$is_list = $this->flowingBlockAttr[ 'is_list' ];
	// width of all the content so far in points
	$contentWidth =& $this->flowingBlockAttr[ 'contentWidth' ];
	// cell width in points
	$maxWidth =& $this->flowingBlockAttr[ 'width' ];
	$lineCount =& $this->flowingBlockAttr[ 'lineCount' ];
	// line height in user units
	$lineHeight =& $this->flowingBlockAttr[ 'height' ];
	$align =& $this->flowingBlockAttr[ 'align' ];
	$content =& $this->flowingBlockAttr[ 'content' ];
	$contentB =& $this->flowingBlockAttr[ 'contentB' ];
	$font =& $this->flowingBlockAttr[ 'font' ];
	$valign =& $this->flowingBlockAttr[ 'valign' ];
	$blockstate = $this->flowingBlockAttr[ 'blockstate' ];

	$newblock = $this->flowingBlockAttr[ 'newblock' ];
	$blockdir = $this->flowingBlockAttr['blockdir'];
	// *********** BLOCK BACKGROUND COLOR ***************** //
	if ($this->blk[$this->blklvl]['bgcolor'] && !$is_table) {
		$fill = 0;
	}
	else {
		$this->SetFColor($this->ConvertColor(255));
		$fill = 0;
	}
	$font[] = $this->saveFont();
	$content[] = '';
	$contentB[] = '';
	$currContent =& $content[ count( $content ) - 1 ];
	// where the line should be cutoff if it is to be justified
	$cutoffWidth = $contentWidth;

	$CJKoverflow = false;
	$hanger = '';	// mPDF 5.6.40

	// COLS
	$oldcolumn = $this->CurrCol;

	if ($this->ColActive && !$is_table) { $this->breakpoints[$this->CurrCol][] = $this->y; }	// *COLUMNS*

/*-- TABLES --*/
   if ($is_table) { 
	$ipaddingL = 0; 
	$ipaddingR = 0; 
	$paddingL = 0; 
	$paddingR = 0; 
	$cpaddingadjustL = 0;
	$cpaddingadjustR = 0;
 	// Added mPDF 3.0
	$fpaddingR = 0;
	$fpaddingL = 0;
  } 
   else { 
/*-- END TABLES --*/
		$ipaddingL = $this->blk[$this->blklvl]['padding_left']; 
		$ipaddingR = $this->blk[$this->blklvl]['padding_right']; 
		$paddingL = ($ipaddingL * _MPDFK); 
		$paddingR = ($ipaddingR * _MPDFK); 
		$this->cMarginL =  $this->blk[$this->blklvl]['border_left']['w'];
		$cpaddingadjustL = -$this->cMarginL;
		$this->cMarginR =  $this->blk[$this->blklvl]['border_right']['w'];
		$cpaddingadjustR = -$this->cMarginR;
		// Added mPDF 3.0 Float DIV
		$fpaddingR = 0;
		$fpaddingL = 0;
/*-- CSS-FLOAT --*/
		if (count($this->floatDivs)) {
			list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl);
			if ($r_exists) { $fpaddingR = $r_width; }
			if ($l_exists) { $fpaddingL = $l_width; }
		}
/*-- END CSS-FLOAT --*/

		$usey = $this->y + 0.002;
		if (($newblock) && ($blockstate==1 || $blockstate==3) && ($lineCount == 0) ) { 
			$usey += $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'];
		}
/*-- CSS-IMAGE-FLOAT --*/
		// If float exists at this level
		if (isset($this->floatmargins['R']) && $usey <= $this->floatmargins['R']['y1'] && $usey >= $this->floatmargins['R']['y0'] && !$this->floatmargins['R']['skipline']) { $fpaddingR += $this->floatmargins['R']['w']; }
		if (isset($this->floatmargins['L']) && $usey <= $this->floatmargins['L']['y1'] && $usey >= $this->floatmargins['L']['y0'] && !$this->floatmargins['L']['skipline']) { $fpaddingL += $this->floatmargins['L']['w']; }
/*-- END CSS-IMAGE-FLOAT --*/
   }	// *TABLES*

     //OBJECTS - IMAGES & FORM Elements (NB has already skipped line/page if required - in printbuffer)
      if (substr($s,0,3) == "\xbb\xa4\xac") { //identifier has been identified!
		$objattr = $this->_getObjAttr($s);
		$h_corr = 0; 
		if ($is_table) {	// *TABLES*
			$maximumW = ($maxWidth/_MPDFK) - ($this->cellPaddingL + $this->cMarginL + $this->cellPaddingR + $this->cMarginR); 	// *TABLES*
		}	// *TABLES*
		else {	// *TABLES*
			if (($newblock) && ($blockstate==1 || $blockstate==3) && ($lineCount == 0) && (!$is_table)) { $h_corr = $this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w']; }
			$maximumW = ($maxWidth/_MPDFK) - ($this->blk[$this->blklvl]['padding_left'] + $this->blk[$this->blklvl]['border_left']['w'] + $this->blk[$this->blklvl]['padding_right'] + $this->blk[$this->blklvl]['border_right']['w'] + $fpaddingL + $fpaddingR ); 
		}	// *TABLES*
		$objattr = $this->inlineObject($objattr['type'],$this->lMargin + $fpaddingL + ($contentWidth/_MPDFK),($this->y + $h_corr), $objattr, $this->lMargin,($contentWidth/_MPDFK),$maximumW,$lineHeight,true,$is_table);

		// SET LINEHEIGHT for this line ================ RESET AT END
		$lineHeight = MAX($lineHeight,$objattr['OUTER-HEIGHT']);
		$this->objectbuffer[count($content)-1] = $objattr;
		// if (isset($objattr['vertical-align'])) { $valign = $objattr['vertical-align']; }
		// else { $valign = ''; }
		$contentWidth += ($objattr['OUTER-WIDTH'] * _MPDFK);
		return;
	}

	$lbw = $rbw = 0;	// Border widths
	if (!empty($this->spanborddet)) { 
		if (isset($this->spanborddet['L'])) $lbw = $this->spanborddet['L']['w'];
		if (isset($this->spanborddet['R'])) $rbw = $this->spanborddet['R']['w'];
	}

   if ($this->usingCoreFont) {
	$tmp = strlen( $s );
   }
   else {
	$tmp = mb_strlen( $s, $this->mb_enc );
   }

   // for every character in the string
   for ( $i = 0; $i < $tmp; $i++ )  {
	// extract the current character
	// get the width of the character in points
	if ($this->usingCoreFont) {
       	$c = $s[$i];
		// Soft Hyphens chr(173)
		$cw = ($this->GetCharWidthCore($c) * _MPDFK);
		if ($this->kerning && $this->useKerning && $i > 0) {
			if (isset($this->CurrentFont['kerninfo'][$s[($i-1)]][$c])) { 
				$cw += ($this->CurrentFont['kerninfo'][$s[($i-1)]][$c] * $this->FontSizePt / 1000 );
			}
		}
	}
	else {
	      $c = mb_substr($s,$i,1,$this->mb_enc );
		$cw = ($this->GetCharWidthNonCore($c, false) * _MPDFK);
		if ($this->kerning && $this->useKerning && $i > 0) {
	     		$lastc = mb_substr($s,($i-1),1,$this->mb_enc );
			$ulastc = $this->UTF8StringToArray($lastc, false);
			$uc = $this->UTF8StringToArray($c, false);
			if (isset($this->CurrentFont['kerninfo'][$ulastc[0]][$uc[0]])) { 
				$cw += ($this->CurrentFont['kerninfo'][$ulastc[0]][$uc[0]] * $this->FontSizePt / 1000 );
			}
		}
	}

	if ($i==0) {
		$cw += $lbw*_MPDFK;
		$contentB[(count($contentB)-1)] .= 'L';
	}
	if ($i==($tmp-1)) {
		$cw += $rbw*_MPDFK;
		$contentB[(count($contentB)-1)] .= 'R';
	}
	// mPDF 5.6.45
	if ($c==' ') { 
		$currContent .= $c;
		$cutoffWidth = $contentWidth;
		$contentWidth += $cw;
		continue;
	}

	// ADDED for Paragraph_indent
	$WidthCorrection = 0; 
	if (($newblock) && ($blockstate==1 || $blockstate==3) && isset($this->blk[$this->blklvl]['text_indent']) && ($lineCount == 0) && (!$is_table) && (!$is_list) && ($align != 'C')) { 
		$ti = $this->ConvertSize($this->blk[$this->blklvl]['text_indent'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); 
		$WidthCorrection = ($ti*_MPDFK); 
	} 

	// Added mPDF 3.0 Float DIV
	$fpaddingR = 0;
	$fpaddingL = 0;
/*-- CSS-FLOAT --*/
	if (count($this->floatDivs)) {
		list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl);
		if ($r_exists) { $fpaddingR = $r_width; }
		if ($l_exists) { $fpaddingL = $l_width; }
	}
/*-- END CSS-FLOAT --*/

	$usey = $this->y + 0.002;
	if (($newblock) && ($blockstate==1 || $blockstate==3) && ($lineCount == 0) ) { 
		$usey += $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'];
	}

/*-- CSS-IMAGE-FLOAT --*/
	// If float exists at this level
	if (isset($this->floatmargins['R']) && $usey <= $this->floatmargins['R']['y1'] && $usey >= $this->floatmargins['R']['y0'] && !$this->floatmargins['R']['skipline']) { $fpaddingR += $this->floatmargins['R']['w']; }
	if (isset($this->floatmargins['L']) && $usey <= $this->floatmargins['L']['y1'] && $usey >= $this->floatmargins['L']['y0'] && !$this->floatmargins['L']['skipline']) { $fpaddingL += $this->floatmargins['L']['w']; }
/*-- END CSS-IMAGE-FLOAT --*/



       // try adding another char
	if (( $contentWidth + $cw > $maxWidth - $WidthCorrection - (($this->cMarginL+$this->cMarginR)*_MPDFK) - ($paddingL+$paddingR +(($fpaddingL + $fpaddingR) * _MPDFK) ) +  0.001))  {// 0.001 is to correct for deviations converting mm=>pts
		// it won't fit, output what we already have
		$lineCount++;

		// contains any content that didn't make it into this print
		$savedContent = '';
		$savedContentB = '';
		$savedFont = array();
		$savedObj = array();
		// mPDF 5.6.20
		$savedPreContent = array();
		$savedPreContentB = array();
		$savedPreFont = array();

		// cut off and save any partial words at the end of the string
		$words = explode( ' ', $currContent ); 
		///////////////////
		// HYPHENATION
		$currWord = $words[count($words)-1] ;
		$success = false;

		// mPDF 5.6.21   Hard Hyphens -
		$hardsuccess = false;
		if ($this->textparam['hyphens'] != 2 && preg_match("/\-/",$currWord)) {	
			$rem = $maxWidth - $WidthCorrection - (($this->cMarginL+$this->cMarginR)*_MPDFK) - ($paddingL+$paddingR +(($fpaddingL + $fpaddingR) * _MPDFK) );
			list($hardsuccess,$pre,$post,$prelength) = $this->hardHyphenate($currWord, (($rem-$cutoffWidth)/_MPDFK -$this->GetCharWidth("-", false)) );
			if ($hardsuccess) { 
				$already = array_pop( $words );
				$forward = mb_substr($already,$prelength+1,mb_strlen($already, $this->mb_enc), $this->mb_enc);
				$words[] = $pre.'-';
				$words[] = $forward;
				$currContent = mb_substr($currContent,0,mb_strlen($currContent, $this->mb_enc)+1-mb_strlen($post, $this->mb_enc), $this->mb_enc) . '-';
			}
		}
		
/*-- HYPHENATION --*/
		// Soft Hyphens chr(173)
		else if ($this->textparam['hyphens'] != 2 && (!$this->usingCoreFont && preg_match("/\xc2\xad/",$currWord)) || ($this->usingCoreFont && preg_match("/".chr(173)."/",$currWord) && ($this->FontFamily!='csymbol' && $this->FontFamily!='czapfdingbats')) ) {	// mPDF 5.6.06
			$rem = $maxWidth - $WidthCorrection - (($this->cMarginL+$this->cMarginR)*_MPDFK) - ($paddingL+$paddingR +(($fpaddingL + $fpaddingR) * _MPDFK) );
			list($success,$pre,$post,$prelength) = $this->softHyphenate($currWord, (($rem-$cutoffWidth)/_MPDFK -$this->GetCharWidth(" ", false)) );
		}

		if (!$success && !$hardsuccess && $this->textparam['hyphens'] == 1 ) { 	// mPDF 5.6.06	// mPDF 5.6.21
			// Look ahead to get current word
			for($ac = $i; $ac<(mb_strlen($s)-1); $ac++) {
				$addc = mb_substr($s,$ac,1,$this->mb_enc );
				if ($addc == ' ') { break; }
				$currWord .= $addc;
			}
			$rem = $maxWidth - $WidthCorrection - (($this->cMarginL+$this->cMarginR)*_MPDFK) - ($paddingL+$paddingR +(($fpaddingL + $fpaddingR) * _MPDFK) );
			list($success,$pre,$post,$prelength) = $this->hyphenateWord($currWord, (($rem-$cutoffWidth)/_MPDFK -$this->GetCharWidth(" ", false)) );
		}
		if ($success) { 
			$already = array_pop( $words );
			$forward = mb_substr($already,$prelength,mb_strlen($already, $this->mb_enc), $this->mb_enc);
			$words[] = $pre.'-';
			$words[] = $forward;
			$currContent = mb_substr($currContent,0,mb_strlen($currContent, $this->mb_enc)+1-mb_strlen($post, $this->mb_enc), $this->mb_enc) . '-';
		}
/*-- END HYPHENATION --*/

		// mPDF 5.6.13 Decimal alignment (cancel if wraps to > 1 line)
		if ($is_table && substr($align,0,1)=='D' ) { $align=substr($align,2,1); }

/*-- CJK-FONTS --*/
		// mPDF 5.6.42
		if ($this->checkCJK) {
			$lastchar = mb_substr($words[(count($words)-1)],mb_strlen($words[(count($words)-1)], $this->mb_enc)-1, 1, $this->mb_enc);
		}
		// Next character is suitable to add as overhanging or squeezed punctuation
		if ($this->checkCJK && preg_match("/[".$this->CJKoverflow."]/u", $c) && $this->allowCJKorphans && !$CJKoverflow) {
			// add character onto this line
			$currContent .= $c;
			$cutoffWidth = $contentWidth;
			$contentWidth += $cw;
			$CJKoverflow = true;
			continue;
		}
		// Last character that fits is not allowed to end a line - move lastchar(s) to start of next line
		else if ($this->checkCJK && preg_match("/[".$this->CJKleading."]/u", $lastchar)) {
			//move lastchar(s) to next line
			$m0 = $lastchar;
			$m1 = $c;
			while(preg_match("/[".$this->CJKleading."]/u", $m0) && mb_strlen($words[(count($words)-1)], $this->mb_enc)>2) {
				// trim last letter off word[0]
				$words[(count($words)-1)] = mb_substr($words[(count($words)-1)],0,mb_strlen($words[(count($words)-1)], $this->mb_enc)-1, $this->mb_enc);
				// and add it to savedContent for next line
				$savedContent = $m0.$savedContent;
				$m1 = $lastchar;
				$lastchar = mb_substr($words[(count($words)-1)],mb_strlen($words[(count($words)-1)], $this->mb_enc)-1, 1, $this->mb_enc);
				$m0 = $lastchar;
			}
			$lastContent = '';
			for ( $w = 0; $w < count( $words ) ; $w++) { $lastContent .= $words[ $w ]." "; }
			$savedFont = $this->saveFont();
			// replace the current content with the cropped version
			$currContent = rtrim( $lastContent );
		}
		// Next character is not allowed to start a new line
		else if ($this->checkCJK && preg_match("/[".$this->CJKfollowing."]/u", $c)) {
			// try squeezing another character(s) onto this line = Oikomi
			if ($this->allowCJKorphans && !$CJKoverflow) {	// mPDF 5.6.40
			      $lookahead = mb_substr($s,$i+1,1,$this->mb_enc );
				//if lookahead is not another following char
				if (!$lookahead || ($lookahead && !preg_match("/[".$this->CJKfollowing."]/u", $lookahead))) {
					$currContent .= $c;
					$cutoffWidth = $contentWidth;
					$contentWidth += $cw;
					if ($this->allowCJKoverflow && preg_match("/[".$this->CJKoverflow."]/u", $c)) { $CJKoverflow = true; }
					continue;
				}
			}
			// or move lastchar(s) to next line to keep $c company = Oidashi
			$m0 = $lastchar;
			$m1 = $c;
			while(preg_match("/[".$this->CJKfollowing."]/u", $m1) && mb_strlen($words[(count($words)-1)], $this->mb_enc)>2) {
				// trim last letter off word[0]
				$words[(count($words)-1)] = mb_substr($words[(count($words)-1)],0,mb_strlen($words[(count($words)-1)], $this->mb_enc)-1, $this->mb_enc);
				// and add it to savedContent for next line
				$savedContent = $m0.$savedContent;
				$m1 = $lastchar;
				$lastchar = mb_substr($words[(count($words)-1)],mb_strlen($words[(count($words)-1)], $this->mb_enc)-1, 1, $this->mb_enc);
				$m0 = $lastchar;
			}
			$lastContent = '';
			for ( $w = 0; $w < count( $words ) ; $w++) { $lastContent .= $words[ $w ]." "; }
			$savedFont = $this->saveFont();
			// replace the current content with the cropped version
			$currContent = rtrim( $lastContent );
		}
		// mPDF 5.6.42
	  	else if ($this->checkCJK && preg_match("/([".$this->pregCJKchars."]+[0-9\x{ff10}-\x{ff19}]+$)/u", $words[0])) {
			$lookahead = mb_substr($s,$i,16,$this->mb_enc );
			//and if lookahead starts with a few numerals
			if ($lookahead && (preg_match("/^([0-9\x{ff10}-\x{ff19}]+[".$this->pregCJKchars."]+)/u", $lookahead) || preg_match("/^([0-9\x{ff10}-\x{ff19}]+$)/u", $lookahead)) ) {
				// or move lastchar(s) to next line to keep numerals together
				$m0 = $lastchar;
				while(preg_match("/[0-9\x{ff10}-\x{ff19}]/u", $m0) && mb_strlen($words[(count($words)-1)], $this->mb_enc)>2) {
					// trim last letter off word[0]
					$words[(count($words)-1)] = mb_substr($words[(count($words)-1)],0,mb_strlen($words[(count($words)-1)], $this->mb_enc)-1, $this->mb_enc);
					// and add it to savedContent for next line
					$savedContent = $m0.$savedContent;
					$lastchar = mb_substr($words[(count($words)-1)],mb_strlen($words[(count($words)-1)], $this->mb_enc)-1, 1, $this->mb_enc);
					$m0 = $lastchar;
				}
			}
			$lastContent = '';
			for ( $w = 0; $w < count( $words ) ; $w++) { $lastContent .= $words[ $w ]." "; }
			$savedFont = $this->saveFont();
			// replace the current content with the cropped version
			$currContent = rtrim( $lastContent );
		}
		else 
/*-- END CJK-FONTS --*/
		// if it looks like we didn't finish any words for this chunk
		if ( count( $words ) == 1 ) {
		  // TO correct for error when word too wide for page - but only when one long word from left to right margin
		  if (count($content) == 1 && $currContent != ' ') {
			$lastchar = mb_substr($words[0],mb_strlen($words[0], $this->mb_enc)-1, 1, $this->mb_enc);
			$lastContent = $words[0]; 
			$savedFont = $this->saveFont();
			// replace the current content with the cropped version
			$currContent = rtrim( $lastContent );
		  }
		  // mPDF 5.6.20
		  else if (	count($content)>1
				&& (!isset($this->objectbuffer[(count($content)-1)]) && !isset($this->objectbuffer[(count($content)-2)]))
				&& substr($content[count($content)-2],-1,1) != ' '
				&& substr($currContent,0,1) != ' '
			) {
			// Go back to find a space in a previous chunk of content
			$found = false;
			for ($ix=count($content)-1;$ix>=0;$ix--) {
				// mPDF 5.6.29
				if ($this->usingCoreFont && preg_match('/[ '.chr(173).']/',$content[$ix],$m)) { $match = $m[0]; $found = $ix; break; }
				else if (!$this->usingCoreFont) {
					if (preg_match('/[ ]/',$content[$ix])) { $match = ' '; $found = $ix; break; }
					else if (preg_match('/[\x{00AD}]/u',$content[$ix])) {
						// even though it is UTF-8 replace it temporarily with chr(173)
						$content[$ix] = preg_replace('/[\x{00AD}]/u',chr(173),$content[$ix]);
						$match = chr(173); $found = $ix; break; 
					}
				}
			}
			if ($found !== false) {
				$charpos = strrpos($content[$found],$match);	// mPDF 5.6.29
				for ($ix=count($content)-1;$ix>$found;$ix--) {
					// save and crop off any subsequent chunks
					$savedPreContent[] = array_pop($content);
					$savedPreContentB[] = array_pop($contentB);
					$savedPreFont[] = array_pop($font);
				}
				if (substr($content[count($content)-1],$charpos+1,strlen($content[count($content)-1])-1) != '') {
					$savedPreContent[] = substr($content[count($content)-1],$charpos+1,strlen($content[count($content)-1])-1);
					$savedPreContentB[] = preg_replace('/L/','',$contentB[count($content)-1]);	// ???
					$savedPreFont[] = $font[count($content)-1];
				}
				$savedContent = '';
				$savedContentB = '';
				$savedFont = array();

				$currContent =& $content[ count( $content ) - 1 ];
				$currContent = substr($currContent,0,$charpos);
				if ($match == chr(173)) { $currContent .= '-'; }	// mPDF 5.6.29
				if (strpos($contentB[(count($contentB)-1)],'R')!==false) {			// ???
					$contentB[count($content)-1] = preg_replace('/R/','',$contentB[count($content)-1]);	// ???
				}

				$currContent = rtrim( $currContent );
			}
			else {
				$savedContent = array_pop( $content );
				$savedContentB = array_pop($contentB);
				$savedFont = array_pop( $font );
				$currContent =& $content[ count( $content ) - 1 ];
				$currContent = rtrim( $currContent );
			}
		  }
		  else {
			// save and crop off the content currently on the stack
			$savedContent = array_pop( $content );
			$savedContentB = array_pop($contentB);	// mPDF 5.6.20
			$savedFont = array_pop( $font );
			// trim any trailing spaces off the last bit of content
			$currContent =& $content[ count( $content ) - 1 ];
			$currContent = rtrim( $currContent );
		  }
		}
		else {	// otherwise, we need to find which bit to cut off
		  $lastContent = '';
              for ( $w = 0; $w < count( $words ) - 1; $w++) { $lastContent .= $words[ $w ]." "; }
              $savedContent = $words[ count( $words ) - 1 ];
              $savedFont = $this->saveFont();
              // replace the current content with the cropped version
              $currContent = rtrim( $lastContent );
		}
		// CJK - strip CJK space at end of line
		// &#x3000; = \xe3\x80\x80 = CJK space
		if ($this->checkCJK) { $currContent = preg_replace("/\xe3\x80\x80$/",'',$currContent) ; }	// *CJK-FONTS*


		if (isset($this->objectbuffer[(count($content)-1)]) && $this->objectbuffer[(count($content)-1)]['type']=='dottab') {
			$savedObj = array_pop( $this->objectbuffer );
			$contentWidth -= ($this->objectbuffer[(count($content)-1)]['OUTER-WIDTH'] * _MPDFK);
		}

		// Set Current lineheight (correction factor)
		$lhfixed = false; 
/*-- LISTS --*/
		if ($is_list) {
			if (preg_match('/([0-9.,]+)mm/',$this->list_lineheight[$this->listlvl][$this->listOcc],$am)) { 
				$lhfixed = true; 
				$def_fontsize = $this->InlineProperties['LISTITEM'][$this->listlvl][$this->listOcc][$this->listnum]['size'];
				$this->lineheight_correction = $am[1] / $def_fontsize ;
			}
			else { 
				$this->lineheight_correction = $this->list_lineheight[$this->listlvl][$this->listOcc]; 
			}
		}
		else
/*-- END LISTS --*/
/*-- TABLES --*/
		if ($is_table) {
			if (preg_match('/([0-9.,]+)mm/',$this->table_lineheight,$am)) { 
				$lhfixed = true; 
				$def_fontsize = $this->FontSize; 				// needs to be default font-size for block ****
				$this->lineheight_correction = $lineHeight / $def_fontsize ; 
			}
			else { 
				$this->lineheight_correction = $this->table_lineheight; 
			}
		}
		else
/*-- END TABLES --*/
		if (isset($this->blk[$this->blklvl]['line_height']) && $this->blk[$this->blklvl]['line_height']) {
			if (preg_match('/([0-9.,]+)mm/',$this->blk[$this->blklvl]['line_height'],$am)) { 
				$lhfixed = true; 
				$def_fontsize = $this->blk[$this->blklvl]['InlineProperties']['size']; 	// needs to be default font-size for block ****
				$this->lineheight_correction = $am[1] / $def_fontsize ;
			}
			else { 
				$this->lineheight_correction = $this->blk[$this->blklvl]['line_height']; 
			}
		} 
		else {
			$this->lineheight_correction = $this->normalLineheight; 
		}
		// update $contentWidth and $cutoffWidth since they changed with cropping
		// Also correct lineheight to maximum fontsize (not for tables)
		$contentWidth = 0;
		//  correct lineheight to maximum fontsize
		if ($lhfixed) { $maxlineHeight = $this->lineheight; }
		else { $maxlineHeight = 0; }
		$this->forceExactLineheight = true;
		$maxfontsize = 0;
		// While we're at it, check for cursive text
		$checkCursive=false;
		if ($this->biDirectional) {  $checkCursive=true; }	// *RTL*
		foreach ( $content as $k => $chunk )
		{
              $this->restoreFont( $font[ $k ]);
		  if (!isset($this->objectbuffer[$k])) { 
			// mPDF 5.6.40
			if ($this->checkCJK && $k == count($content)-1 && $CJKoverflow && $align=='J' && $this->allowCJKoverflow && $this->CJKforceend) {
			  // force-end overhang
				$hanger = mb_substr($chunk,mb_strlen($chunk,$this->mb_enc)-1,1,$this->mb_enc );
				$content[$k] = $chunk = mb_substr($chunk,0,mb_strlen($chunk,$this->mb_enc)-1,$this->mb_enc );
			}
			if (!$this->usingCoreFont) {
			      $content[$k] = $chunk = str_replace("\xc2\xad",'',$chunk ); 
				if (isset($this->CurrentFont['indic']) && $this->CurrentFont['indic']) {  $checkCursive=true; }	// *INDIC*
			}
			// Soft Hyphens chr(173)
			else if ($this->FontFamily!='csymbol' && $this->FontFamily!='czapfdingbats') {
			      $content[$k] = $chunk = str_replace(chr(173),'',$chunk );
			}
			$contentWidth += $this->GetStringWidth( $chunk ) * _MPDFK; 
			if (!empty($this->spanborddet)) { 
				if (strpos($contentB[$k],'L')!==false) $contentWidth += $this->spanborddet['L']['w'] * _MPDFK; 
				if (strpos($contentB[$k],'R')!==false) $contentWidth += $this->spanborddet['R']['w'] * _MPDFK; 
			}
			if (!$lhfixed) { $maxlineHeight = max($maxlineHeight,$this->FontSize * $this->lineheight_correction ); }
			if ($lhfixed && ($this->FontSize > $def_fontsize || ($this->FontSize > ($lineHeight * $this->lineheight_correction) && $is_list))) { 
				$this->forceExactLineheight = false; 
			}
			$maxfontsize = max($maxfontsize,$this->FontSize); 
		  }
		}

		$lastfontreqstyle = $font[count($font)-1]['ReqFontStyle'];
		$lastfontstyle = $font[count($font)-1]['style'];
		if ($blockdir == 'ltr' && strpos($lastfontreqstyle,"I") !== false && strpos($lastfontstyle,"I") === false) {	// Artificial italic
			$lastitalic = $this->FontSize*0.15*_MPDFK;
		}
		else { $lastitalic = 0; }


/*-- LISTS --*/
		if ($is_list && is_array($this->bulletarray) && $this->bulletarray) {
	  		$actfs = $this->bulletarray['fontsize'];
			if (!$lhfixed) { $maxlineHeight = max($maxlineHeight,$actfs * $this->lineheight_correction );  }
			if ($lhfixed && $actfs > $def_fontsize) { $this->forceExactLineheight = false; }
			$maxfontsize = max($maxfontsize,$actfs);
		}
/*-- END LISTS --*/

		// when every text item checked i.e. $maxfontsize is set properly

		$af = 0; 	// Above font
		$bf = 0; 	// Below font
		$mta = 0;	// Maximum top-aligned 
		$mba = 0;	// Maximum bottom-aligned 

		foreach ( $content as $k => $chunk ) {
		  if (isset($this->objectbuffer[$k]) && $this->objectbuffer[$k]) { 
			$contentWidth += $this->objectbuffer[$k]['OUTER-WIDTH'] * _MPDFK; 
			$oh = $this->objectbuffer[$k]['OUTER-HEIGHT'];
			$va = $this->objectbuffer[$k]['vertical-align']; // = $objattr['vertical-align'] = set as M,T,B,S
			if ($lhfixed && $oh > $def_fontsize) { $this->forceExactLineheight = false; }

			if ($va == 'BS') {	//  (BASELINE default)
				$af = max($af, ($oh - ($maxfontsize * (0.5 + $this->baselineC))));
			}
			else if ($va == 'M') { 
				$af = max($af, ($oh - $maxfontsize)/2);
				$bf = max($bf, ($oh - $maxfontsize)/2);
			}
			else if ($va == 'TT') { 
				$bf = max($bf, ($oh - $maxfontsize));
			}
			else if ($va == 'TB') { 
				$af = max($af, ($oh - $maxfontsize));
			}
			else if ($va == 'T') { 
				$mta = max($mta, $oh);
			}
			else if ($va == 'B') { 
				$mba = max($mba, $oh);
			}
		  }
		}
		if ((!$lhfixed || !$this->forceExactLineheight) && ($af > (($maxlineHeight - $maxfontsize)/2) || $bf > (($maxlineHeight - $maxfontsize)/2))) {
			$maxlineHeight = $maxfontsize + $af + $bf;
		}
		else if (!$lhfixed) { $af = $bf = ($maxlineHeight - $maxfontsize)/2; }

		if ($mta > $maxlineHeight) { 
			$bf += ($mta - $maxlineHeight);
			$maxlineHeight = $mta;
		}
		if ($mba > $maxlineHeight) { 
			$af += ($mba - $maxlineHeight);
			$maxlineHeight = $mba;
		}


		$lineHeight = $maxlineHeight; 
		$cutoffWidth = $contentWidth;
		// If NOT images, and maxfontsize NOT > lineHeight - this value determines text baseline positioning
		if ($lhfixed && $af==0 && $bf==0 && $maxfontsize<=($def_fontsize * $this->lineheight_correction * 0.8 )) { 
			$this->linemaxfontsize = $def_fontsize; 
		}
		else { $this->linemaxfontsize = $maxfontsize; }


		$inclCursive=false;
		foreach ( $content as $k => $chunk ) {
			if (!isset($this->objectbuffer[$k]) || (isset($this->objectbuffer[$k]) && !$this->objectbuffer[$k])) {
				if ($this->usingCoreFont) {
				      $content[$k] = str_replace(chr(160),chr(32),$chunk );
				}
				else {
				      $content[$k] = str_replace(chr(194).chr(160),chr(32),$chunk ); 
					if ($checkCursive) {
						if (preg_match("/([".$this->pregRTLchars."])/u", $chunk)) { $inclCursive = true; }	// *RTL*
						if (preg_match("/([".$this->pregHIchars.$this->pregBNchars.$this->pregPAchars."])/u", $chunk)) { $inclCursive = true; }	// *INDIC*
					}
				}
			}
		}

		// JUSTIFICATION J
		$jcharspacing = 0;
		$jws = 0;
		$nb_carac = 0;
		$nb_spaces = 0;
		// if it's justified, we need to find the char/word spacing (or if orphans have allowed length of line to go over the maxwidth)
		if ( ($align == 'J' && !$CJKoverflow) || (($cutoffWidth + $lastitalic > $maxWidth - $WidthCorrection - (($this->cMarginL+$this->cMarginR)*_MPDFK) - ($paddingL+$paddingR +(($fpaddingL + $fpaddingR) * _MPDFK) ) +  0.001) && (!$CJKoverflow || ($CJKoverflow && !$this->allowCJKoverflow))) || $CJKoverflow && $align=='J' && $this->allowCJKoverflow && $hanger && $this->CJKforceend) {   // 0.001 is to correct for deviations converting mm=>pts	// mPDF 5.6.40
		  // JUSTIFY J (Use character spacing)
 		  // WORD SPACING
			foreach ( $content as $k => $chunk ) {
				if (!isset($this->objectbuffer[$k]) || (isset($this->objectbuffer[$k]) && !$this->objectbuffer[$k])) {
					$nb_carac += mb_strlen( $chunk, $this->mb_enc ) ;  
					$nb_spaces += mb_substr_count( $chunk,' ', $this->mb_enc ) ;  
				}
			}
			list($jcharspacing,$jws) = $this->GetJspacing($nb_carac,$nb_spaces,($maxWidth-$lastitalic-$cutoffWidth-$WidthCorrection-(($this->cMarginL+$this->cMarginR)*_MPDFK)-($paddingL+$paddingR +(($fpaddingL + $fpaddingR) * _MPDFK) )),$inclCursive);
		}


		// WORD SPACING
		$empty = $maxWidth - $lastitalic-$WidthCorrection - $contentWidth - (($this->cMarginL+$this->cMarginR)* _MPDFK) - ($paddingL+$paddingR +(($fpaddingL + $fpaddingR) * _MPDFK) );

		$empty -= ($jcharspacing * $nb_carac);
		$empty -= ($jws * $nb_spaces);

		$empty /= _MPDFK;
		$b = ''; //do not use borders
		// Get PAGEBREAK TO TEST for height including the top border/padding
		$check_h = max($this->divheight,$lineHeight);
		if (($newblock) && ($blockstate==1 || $blockstate==3) && ($this->blklvl > 0) && ($lineCount == 1) && (!$is_table) && (!$is_list)) { 
			$check_h += ($this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['border_top']['w']);
		}

		if ($this->ColActive && $check_h > ($this->PageBreakTrigger - $this->y0)) { 
			$this->SetCol($this->NbCol-1);
		}

		// PAGEBREAK
		// 'If' below used in order to fix "first-line of other page with justify on" bug 
		if(!$is_table && ($this->y+$check_h) > $this->PageBreakTrigger and !$this->InFooter and $this->AcceptPageBreak()) {
			$bak_x=$this->x;//Current X position

			// WORD SPACING
			$ws=$this->ws;//Word Spacing
			$charspacing=$this->charspacing;//Character Spacing
			$this->ResetSpacing();

		      $this->AddPage($this->CurOrientation);

		      $this->x = $bak_x;
			// Added to correct for OddEven Margins
			$currentx += $this->MarginCorrection;
			$this->x += $this->MarginCorrection;

			// WORD SPACING
			$this->SetSpacing($charspacing,$ws);
		}

		if ($this->keep_block_together && !$is_table && $this->kt_p00 < $this->page && ($this->y+$check_h) > $this->kt_y00) {
			$this->printdivbuffer();
			$this->keep_block_together = 0;
		}


/*-- COLUMNS --*/
		// COLS
		// COLUMN CHANGE
		if ($this->CurrCol != $oldcolumn) {
			$currentx += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);
			$this->x += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);
			$oldcolumn = $this->CurrCol;
		}

		if ($this->ColActive && !$is_table) { $this->breakpoints[$this->CurrCol][] = $this->y; }	// *COLUMNS*
/*-- END COLUMNS --*/

		// TOP MARGIN
		if (($newblock) && ($blockstate==1 || $blockstate==3) && ($this->blk[$this->blklvl]['margin_top']) && ($lineCount == 1) && (!$is_table) && (!$is_list)) { 
			$this->DivLn($this->blk[$this->blklvl]['margin_top'],$this->blklvl-1,true,$this->blk[$this->blklvl]['margin_collapse']); 
			if ($this->ColActive) { $this->breakpoints[$this->CurrCol][] = $this->y; }	// *COLUMNS*
		}


		// Update y0 for top of block (used to paint border)
		if (($newblock) && ($blockstate==1 || $blockstate==3) && ($lineCount == 1) && (!$is_table) && (!$is_list)) { 
			$this->blk[$this->blklvl]['y0'] = $this->y;
			$this->blk[$this->blklvl]['startpage'] = $this->page;
			if ($this->blk[$this->blklvl]['float']) { $this->blk[$this->blklvl]['float_start_y'] = $this->y; } // mPDF 5.6.63
		}

		// TOP PADDING and BORDER spacing/fill
		if (($newblock) && ($blockstate==1 || $blockstate==3) && (($this->blk[$this->blklvl]['padding_top']) || ($this->blk[$this->blklvl]['border_top'])) && ($lineCount == 1) && (!$is_table) && (!$is_list)) { 
			// $state = 0 normal; 1 top; 2 bottom; 3 top and bottom
			$this->DivLn($this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'],-3,true,false,1);
			if ($this->ColActive) { $this->breakpoints[$this->CurrCol][] = $this->y; }	// *COLUMNS*
		}

		$arraysize = count($content);

		$margins = ($this->cMarginL+$this->cMarginR) + ($ipaddingL+$ipaddingR + $fpaddingR + $fpaddingR );
 
		// PAINT BACKGROUND FOR THIS LINE
		if (!$is_table) { $this->DivLn($lineHeight,$this->blklvl,false); }	// false -> don't advance y

		$this->x = $currentx + $this->cMarginL + $ipaddingL + $fpaddingL ;
		if ($align == 'R') { $this->x += $empty; }
		else if ($align == 'C') { $this->x += ($empty / 2); }

		// Paragraph INDENT
		if (isset($this->blk[$this->blklvl]['text_indent']) && ($newblock) && ($blockstate==1 || $blockstate==3) && ($lineCount == 1) && (!$is_table) && ($blockdir !='rtl') && ($align !='C')) { 
			$ti = $this->ConvertSize($this->blk[$this->blklvl]['text_indent'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); 
			$this->x += $ti; 
		}

		// DIRECTIONALITY RTL
		$all_rtl = false;
		$contains_rtl = false;
/*-- RTL --*/
   		if ($blockdir == 'rtl' || $this->biDirectional)  {
			$all_rtl = true;
			foreach ( $content as $k => $chunk ) {
				$reversed = $this->magic_reverse_dir($chunk, false, $blockdir);

				if ($reversed > 0) { $contains_rtl = true; }
				if ($reversed < 2) { $all_rtl = false; }
				$content[$k] = $chunk;
			}
			if (($blockdir =='rtl' && $contains_rtl) || $all_rtl) { 
				$content = array_reverse($content,false); 
				$contentB = array_reverse($contentB,false);
			}
		}
/*-- END RTL --*/

		foreach ( $content as $k => $chunk ) {

			// FOR IMAGES - UPDATE POSITION
			if (($blockdir =='rtl' && $contains_rtl) || $all_rtl) { $dirk = $arraysize-1 - $k ; } else { $dirk = $k; }

			$va = 'M';	// default for text
			if (isset($this->objectbuffer[$dirk]) && $this->objectbuffer[$dirk]) {
			  $xadj = $this->x - $this->objectbuffer[$dirk]['OUTER-X'] ; 
			  $this->objectbuffer[$dirk]['OUTER-X'] += $xadj;
			  $this->objectbuffer[$dirk]['BORDER-X'] += $xadj;
			  $this->objectbuffer[$dirk]['INNER-X'] += $xadj;
			  $va = $this->objectbuffer[$dirk]['vertical-align'];
			  $yadj = $this->y - $this->objectbuffer[$dirk]['OUTER-Y'];
			  if ($va == 'BS') { 
				$yadj += $af + ($this->linemaxfontsize * (0.5 + $this->baselineC)) - $this->objectbuffer[$dirk]['OUTER-HEIGHT'];
			  }
			  else if ($va == 'M' || $va == '') { 
				$yadj += $af + ($this->linemaxfontsize /2) - ($this->objectbuffer[$dirk]['OUTER-HEIGHT']/2);
			  }
			  else if ($va == 'TB') { 
				$yadj += $af + $this->linemaxfontsize - $this->objectbuffer[$dirk]['OUTER-HEIGHT'];
			  }
			  else if ($va == 'TT') { 
				$yadj += $af;
			  }
			  else if ($va == 'B') { 
				$yadj += $af + $this->linemaxfontsize + $bf - $this->objectbuffer[$dirk]['OUTER-HEIGHT'];
			  }
			  else if ($va == 'T') { 
				$yadj += 0;
			  }
			  $this->objectbuffer[$dirk]['OUTER-Y'] += $yadj;
			  $this->objectbuffer[$dirk]['BORDER-Y'] += $yadj;
			  $this->objectbuffer[$dirk]['INNER-Y'] += $yadj;
			}

			// DIRECTIONALITY RTL
			if ((($blockdir == 'rtl') && ($contains_rtl )) || ($all_rtl )) { $this->restoreFont($font[$arraysize-1 - $k]); }
			else { $this->restoreFont( $font[ $k ] ); }

			$this->SetSpacing(($this->fixedlSpacing*_MPDFK)+$jcharspacing,($this->fixedlSpacing+$this->minwSpacing)*_MPDFK+$jws);
			// Now unset these values so they don't influence GetStringwidth below or in fn. Cell
			$this->fixedlSpacing = false;
			$this->minwSpacing = 0;

			// mPDF 5.6.26
			$save_vis = $this->visibility;
			if (isset($this->textparam['visibility']) && $this->textparam['visibility'] && $this->textparam['visibility'] != $this->visibility) {
				$this->SetVisibility($this->textparam['visibility']);
			}
	 		// *********** SPAN BACKGROUND COLOR ***************** //
			if ($this->spanbgcolor) { 
				$cor = $this->spanbgcolorarray;
				$this->SetFColor($cor);
				$save_fill = $fill; $spanfill = 1; $fill = 1;
			}
			if (!empty($this->spanborddet)) { 
				if (strpos($contentB[$k],'L')!==false) $this->x += $this->spanborddet['L']['w'];
				if (strpos($contentB[$k],'L')===false) $this->spanborddet['L']['s'] = $this->spanborddet['L']['w'] = 0; 
				if (strpos($contentB[$k],'R')===false) $this->spanborddet['R']['s'] = $this->spanborddet['R']['w'] = 0; 
			}

			// WORD SPACING
		      $stringWidth = $this->GetStringWidth($chunk );
			$stringWidth += ( $this->charspacing * mb_strlen($chunk,$this->mb_enc ) / _MPDFK );
			$stringWidth += ( $this->ws * mb_substr_count($chunk,' ',$this->mb_enc ) / _MPDFK );
			if (isset($this->objectbuffer[$dirk])) { $stringWidth = $this->objectbuffer[$dirk]['OUTER-WIDTH'];  }

			if ($stringWidth==0) { $stringWidth = 0.000001; }
			if ($k == $arraysize-1) {
				$stringWidth -= ( $this->charspacing / _MPDFK ); 
				// mPDF 5.6.40
				if ($this->checkCJK && $CJKoverflow && $align=='J' && $this->allowCJKoverflow && $hanger && $this->CJKforceend) {
				  // force-end overhang
					$this->Cell( $stringWidth, $lineHeight, $chunk, '', 0, '', $fill, $this->HREF, $currentx,0,0,'M', $fill, $af, $bf, true ); 
					$this->Cell( $this->GetStringWidth($hanger), $lineHeight, $hanger, '', 1, '', $fill, $this->HREF, $currentx,0,0,'M', $fill, $af, $bf, true );
				}
				else {
					$this->Cell( $stringWidth, $lineHeight, $chunk, '', 1, '', $fill, $this->HREF, $currentx,0,0,'M', $fill, $af, $bf, true ); //mono-style line or last part (skips line)
				}

			}
			else $this->Cell( $stringWidth, $lineHeight, $chunk, '', 0, '', $fill, $this->HREF, 0, 0,0,'M', $fill, $af, $bf, true );//first or middle part	

			if (!empty($this->spanborddet)) { 
				if (strpos($contentB[$k],'R')!==false && $k != $arraysize-1)  $this->x += $this->spanborddet['R']['w'];
			}
	 		// *********** SPAN BACKGROUND COLOR OFF - RESET BLOCK BGCOLOR ***************** //
			if (isset($spanfill) && $spanfill) { 
				$fill = $save_fill; $spanfill = 0; 
				if ($fill) { $this->SetFColor($bcor); }
			}
			// mPDF 5.6.26
			if (isset($this->textparam['visibility']) && $this->textparam['visibility'] && $this->visibility != $save_vis) {
				$this->SetVisibility($save_vis);
			}

		}
		if (!$is_table) { 
			$this->maxPosR = max($this->maxPosR , ($this->w - $this->rMargin - $this->blk[$this->blklvl]['outer_right_margin'])); 
			$this->maxPosL = min($this->maxPosL , ($this->lMargin + $this->blk[$this->blklvl]['outer_left_margin'])); 
		}

		// move on to the next line, reset variables, tack on saved content and current char

		$this->printobjectbuffer($is_table, $blockdir);
		$this->objectbuffer = array();

/*-- LISTS --*/
		// LIST BULLETS/NUMBERS
		if ($is_list && is_array($this->bulletarray) && ($lineCount == 1) ) {
		  
		  $this->ResetSpacing();

		  $bull = $this->bulletarray;
	  	  if (isset($bull['level']) && isset($bull['occur']) && isset($this->InlineProperties['LIST'][$bull['level']][$bull['occur']])) { 
			$this->restoreInlineProperties($this->InlineProperties['LIST'][$bull['level']][$bull['occur']]); 
		  }
		  if (isset($bull['level']) && isset($bull['occur']) && isset($bull['num']) && isset($this->InlineProperties['LISTITEM'][$bull['level']][$bull['occur']][$bull['num']]) && $this->InlineProperties['LISTITEM'][$bull['level']][$bull['occur']][$bull['num']]) { $this->restoreInlineProperties($this->InlineProperties['LISTITEM'][$bull['level']][$bull['occur']][$bull['num']]); }
	  	  if (isset($bull['font']) && $bull['font'] == 'czapfdingbats') {
			$this->bullet = true;
			$this->SetFont('czapfdingbats','',$this->FontSizePt/2.5);
		  }
		  else { $this->SetFont($this->FontFamily,$this->FontStyle,$this->FontSizePt,true,true); }	// force output
	        //Output bullet
	  	  $this->x = $currentx;
	 	  if (isset($bull['x'])) { $this->x += $bull['x']; }
		  $this->y -= $lineHeight;
		  if (isset($bull['col']) && $bull['col']) { $this->SetTColor($bull['col']); }	// mPDF 5.6.67
		  if (isset($bull['txt'])) { $this->Cell($bull['w'], $lineHeight,$bull['txt'],'','',$bull['align'],0,'',0,-$this->cMarginL, -$this->cMarginR ); }
	  	  if (isset($bull['font']) && $bull['font'] == 'czapfdingbats') {
			$this->bullet = false;
		  }
		  $this->x = $currentx;	// Reset
		  $this->y += $lineHeight;

		  if ($this->ColActive && !$is_table) { $this->breakpoints[$this->CurrCol][] = $this->y; }	// *COLUMNS*

		  $this->bulletarray = array();	// prevents repeat of bullet/number if <li>....<br />.....</li>
		}
/*-- END LISTS --*/


/*-- CSS-IMAGE-FLOAT --*/
		// Update values if set to skipline
		if ($this->floatmargins) { $this->_advanceFloatMargins(); }
/*-- END CSS-IMAGE-FLOAT --*/

		// Reset lineheight
		$lineHeight = $this->divheight;
		$valign = 'M';

		$font = array();
		$content = array();
		$contentB = array();
		$contentWidth = 0;
		if (!empty($savedObj)) {
			$this->objectbuffer[] = $savedObj;
			$font[] = $savedFont;
			$content[] = '';
			$contentB[] = '';
			$contentWidth += $savedObj['OUTER-WIDTH'] * _MPDFK;
		}
		// mPDF 5.6.20
		if (count($savedPreContent) > 0) {
			for($ix=count($savedPreContent)-1;$ix>=0;$ix--) {
				$font[] = $savedPreFont[$ix];
				$content[] = $savedPreContent[$ix];
				$contentB[] = $savedPreContentB[$ix];
				$this->restoreFont( $savedPreFont[$ix] );
				$lbw = $rbw = 0;	// Border widths
				if (!empty($this->spanborddet)) { 
					$lbw = $this->spanborddet['L']['w'];
					$rbw = $this->spanborddet['R']['w'];
				}
				if ($ix>0) {
					$contentWidth += $this->GetStringWidth( $savedPreContent[$ix] ) * _MPDFK;
					if (strpos($savedPreContentB[$ix],'L')!==false) $contentWidth += $lbw;
					if (strpos($savedPreContentB[$ix],'R')!==false) $contentWidth += $rbw;
				}
			}
			$savedPreContent = array();
			$savedPreContentB = array();
			$savedPreFont = array();
			$content[ (count($content)-1) ] .= $c;
		}
		else {
			$font[] = $savedFont;
			$content[] = $savedContent . $c;
			$contentB[] = $savedContentB ;
		}

		$currContent =& $content[ (count($content)-1) ];

		// CJK - strip CJK space at end of line
		// &#x3000; = \xe3\x80\x80 = CJK space
		if ($this->checkCJK && $currContent == "\xe3\x80\x80") { $currContent = '' ; }	// *CJK-FONTS*

		$this->restoreFont( $savedFont );
		$lbw = $rbw = 0;	// Border widths
		if (!empty($this->spanborddet)) { 
			$lbw = $this->spanborddet['L']['w'];
			$rbw = $this->spanborddet['R']['w'];
		}
		$contentWidth += $this->GetStringWidth( $currContent ) * _MPDFK;
		if (strpos($savedContentB,'L')!==false) $contentWidth += $lbw;
		$cutoffWidth = $contentWidth;
		$CJKoverflow = false;
		$hanger = '';	// mPDF 5.6.40
      }
      // another character will fit, so add it on
	else {
		$contentWidth += $cw;
		$currContent .= $c;
	}
    }
    unset($content);
    unset($contentB);
}
//----------------------END OF FLOWING BLOCK------------------------------------//


/*-- CSS-IMAGE-FLOAT --*/
// Update values if set to skipline
function _advanceFloatMargins() {
	// Update floatmargins - L
	if (isset($this->floatmargins['L']) && $this->floatmargins['L']['skipline'] && $this->floatmargins['L']['y0'] != $this->y) {
		$yadj = $this->y - $this->floatmargins['L']['y0'];
		$this->floatmargins['L']['y0'] = $this->y;
		$this->floatmargins['L']['y1'] += $yadj;

		// Update objattr in floatbuffer
		if ($this->floatbuffer[$this->floatmargins['L']['id']]['border_left']['w']) {
			$this->floatbuffer[$this->floatmargins['L']['id']]['BORDER-Y'] += $yadj;
		}
		$this->floatbuffer[$this->floatmargins['L']['id']]['INNER-Y'] += $yadj;
		$this->floatbuffer[$this->floatmargins['L']['id']]['OUTER-Y'] += $yadj;

		// Unset values
		$this->floatbuffer[$this->floatmargins['L']['id']]['skipline'] = false;
		$this->floatmargins['L']['skipline'] = false;
		$this->floatmargins['L']['id'] = '';
	}
	// Update floatmargins - R
	if (isset($this->floatmargins['R']) && $this->floatmargins['R']['skipline'] && $this->floatmargins['R']['y0'] != $this->y) {
		$yadj = $this->y - $this->floatmargins['R']['y0'];
		$this->floatmargins['R']['y0'] = $this->y;
		$this->floatmargins['R']['y1'] += $yadj;

		// Update objattr in floatbuffer
		if ($this->floatbuffer[$this->floatmargins['R']['id']]['border_left']['w']) {
			$this->floatbuffer[$this->floatmargins['R']['id']]['BORDER-Y'] += $yadj;
		}
		$this->floatbuffer[$this->floatmargins['R']['id']]['INNER-Y'] += $yadj;
		$this->floatbuffer[$this->floatmargins['R']['id']]['OUTER-Y'] += $yadj;

		// Unset values
		$this->floatbuffer[$this->floatmargins['R']['id']]['skipline'] = false;
		$this->floatmargins['R']['skipline'] = false;
		$this->floatmargins['R']['id'] = '';
	}
}
/*-- END CSS-IMAGE-FLOAT --*/



////////////////////////////////////////////////////////////////////////////////
// ADDED forcewrap - to call from inline OBJECT functions to breakwords if necessary in cell
////////////////////////////////////////////////////////////////////////////////
function WordWrap(&$text, $maxwidth, $forcewrap = 0) {
    $biggestword=0;
    $toonarrow=false;

    $text = trim($text);

    if ($text==='') return 0;
    $space = $this->GetCharWidth(' ',false);
    $lines = explode("\n", $text);
    $text = '';
    $count = 0;
    foreach ($lines as $line) {
	$words = explode(' ', $line);
	$width = 0;
	foreach ($words as $word) {
		$word = trim($word);
		$wordwidth = $this->GetStringWidth($word);
		//Warn user that maxwidth is insufficient
		if ($wordwidth > $maxwidth + 0.0001) {
			if ($wordwidth > $biggestword) { $biggestword = $wordwidth; }
			$toonarrow=true;
			// ADDED
			if ($forcewrap) {
			  while($wordwidth > $maxwidth) {
				$chw = 0;	// check width
				for ( $i = 0; $i < mb_strlen($word, $this->mb_enc ); $i++ ) {
					$chw = $this->GetStringWidth(mb_substr($word,0,$i+1,$this->mb_enc ));
					if ($chw > $maxwidth ) {
						if ($text) {
							$text = rtrim($text)."\n".mb_substr($word,0,$i,$this->mb_enc );
							$count++;
						}
						else {
							$text = mb_substr($word,0,$i,$this->mb_enc );
						}
						$word = mb_substr($word,$i,mb_strlen($word, $this->mb_enc )-$i,$this->mb_enc );
						$wordwidth = $this->GetStringWidth($word);
						$width = $maxwidth; 
						break;
					}
				}
			  }
			}
		}

		if ($width + $wordwidth  < $maxwidth - 0.0001) {
			$width += $wordwidth + $space;
			$text .= $word.' ';
		}
		else {
			$width = $wordwidth + $space;
			$text = rtrim($text)."\n".$word.' ';
			$count++;
            }
	}
	$text .= "\n";
	$count++;
    }
    $text = rtrim($text);

    //Return -(wordsize) if word is bigger than maxwidth 

	// ADDED
	if ($forcewrap) { return $count; }
      if (($toonarrow) && ($this->table_error_report)) {
		$this->Error("Word is too long to fit in table - ".$this->table_error_report_param); 
	}
    if ($toonarrow) return -$biggestword;
    else return $count;
}

/*-- END HTML-CSS --*/

function _SetTextRendering($mode) { 
	if (!(($mode == 0) || ($mode == 1) || ($mode == 2))) 
	$this->Error("Text rendering mode should be 0, 1 or 2 (value : $mode)"); 
	$tr = ($mode.' Tr'); 
	if($this->page>0 && ((isset($this->pageoutput[$this->page]['TextRendering']) && $this->pageoutput[$this->page]['TextRendering'] != $tr) || !isset($this->pageoutput[$this->page]['TextRendering']) || $this->keep_block_together)) { $this->_out($tr); }
	$this->pageoutput[$this->page]['TextRendering'] = $tr;

} 

function SetTextOutline($params=array()) {	// mPDF 5.6.07
  if (isset($params['outline-s']) && $params['outline-s'])
  { 
    $this->SetLineWidth($params['outline-WIDTH']); 
    $this->SetDColor($params['outline-COLOR']);
    $tr = ('2 Tr'); 
	if($this->page>0 && ((isset($this->pageoutput[$this->page]['TextRendering']) && $this->pageoutput[$this->page]['TextRendering'] != $tr) || !isset($this->pageoutput[$this->page]['TextRendering']) || $this->keep_block_together)) { $this->_out($tr); }
	$this->pageoutput[$this->page]['TextRendering'] = $tr;
  }
  else //Now resets all values
  { 
    $this->SetLineWidth(0.2); 
    $this->SetDColor($this->ConvertColor(0));
    $this->_SetTextRendering(0); 
    $tr = ('0 Tr'); 
	if($this->page>0 && ((isset($this->pageoutput[$this->page]['TextRendering']) && $this->pageoutput[$this->page]['TextRendering'] != $tr) || !isset($this->pageoutput[$this->page]['TextRendering']) || $this->keep_block_together)) { $this->_out($tr); }
	$this->pageoutput[$this->page]['TextRendering'] = $tr;
  } 
}

function Image($file,$x,$y,$w=0,$h=0,$type='',$link='',$paint=true, $constrain=true, $watermark=false, $shownoimg=true, $allowvector=true) {
	$orig_srcpath = $file;
	$this->GetFullPath($file);

	$info=$this->_getImage($file, true, $allowvector, $orig_srcpath );
	if(!$info && $paint) {
		$info = $this->_getImage($this->noImageFile);
		if ($info) { 
			$file = $this->noImageFile; 
			$w = ($info['w'] * (25.4/$this->dpi)); 	// 14 x 16px
			$h = ($info['h'] * (25.4/$this->dpi)); 	// 14 x 16px
		}
	}
	if(!$info) return false;
	//Automatic width and height calculation if needed
	if($w==0 and $h==0) {
/*-- IMAGES-WMF --*/
           if ($info['type']=='wmf') { 
			// WMF units are twips (1/20pt)
			// divide by 20 to get points
			// divide by k to get user units
			$w = abs($info['w'])/(20*_MPDFK);
			$h = abs($info['h']) / (20*_MPDFK);
		}
		else 
/*-- END IMAGES-WMF --*/
           if ($info['type']=='svg') { 
			// returned SVG units are pts
			// divide by k to get user units (mm)
			$w = abs($info['w'])/_MPDFK;
			$h = abs($info['h']) /_MPDFK;
		}
		else {
			//Put image at default image dpi
			$w=($info['w']/_MPDFK) * (72/$this->img_dpi);
			$h=($info['h']/_MPDFK) * (72/$this->img_dpi);
		}
	}
	if($w==0)	$w=abs($h*$info['w']/$info['h']); 
	if($h==0)	$h=abs($w*$info['h']/$info['w']); 

/*-- WATERMARK --*/
	if ($watermark) {
	  $maxw = $this->w;
	  $maxh = $this->h;
	  // Size = D PF or array
	  if (is_array($this->watermark_size)) {
		$w = $this->watermark_size[0];
		$h = $this->watermark_size[1];
	  }
	  else if (!is_string($this->watermark_size)) {
		$maxw -= $this->watermark_size*2;
		$maxh -= $this->watermark_size*2;
		$w = $maxw;
		$h=abs($w*$info['h']/$info['w']);
		if ($h > $maxh )  {
			$h = $maxh ; $w=abs($h*$info['w']/$info['h']);
		}
	  }
	  else if ($this->watermark_size == 'F') {
		if ($this->ColActive) { $maxw = $this->w - ($this->DeflMargin + $this->DefrMargin); }
		else { $maxw = $this->pgwidth; }
		$maxh = $this->h - ($this->tMargin + $this->bMargin);
		$w = $maxw;
		$h=abs($w*$info['h']/$info['w']);
		if ($h > $maxh )  {
			$h = $maxh ; $w=abs($h*$info['w']/$info['h']);
		}
	  }
	  else  if ($this->watermark_size == 'P') {	// Default P
		$w = $maxw;
		$h=abs($w*$info['h']/$info['w']);
		if ($h > $maxh )  {
			$h = $maxh ; $w=abs($h*$info['w']/$info['h']);
		}
	  }
	  // Automatically resize to maximum dimensions of page if too large
	  if ($w > $maxw) {
		$w = $maxw;
		$h=abs($w*$info['h']/$info['w']);
	  }
	  if ($h > $maxh )  {
		$h = $maxh ;
		$w=abs($h*$info['w']/$info['h']);
	  }
	  // Position
	  if (is_array($this->watermark_pos)) {
		$x = $this->watermark_pos[0];
		$y = $this->watermark_pos[1];
	  }
	  else if ($this->watermark_pos == 'F')  {	// centred on printable area
		if ($this->ColActive) {	// *COLUMNS*
			if (($this->mirrorMargins) && (($this->page)%2==0)) { $xadj = $this->DeflMargin-$this->DefrMargin; }	// *COLUMNS*
			else { $xadj = 0; }	// *COLUMNS*
			$x = ($this->DeflMargin - $xadj + ($this->w - ($this->DeflMargin + $this->DefrMargin))/2) - ($w/2);	// *COLUMNS*
		}	// *COLUMNS*
		else { 	// *COLUMNS*
			$x = ($this->lMargin + ($this->pgwidth)/2) - ($w/2);
		}	// *COLUMNS*
		$y = ($this->tMargin + ($this->h - ($this->tMargin + $this->bMargin))/2) - ($h/2);
	  }
	  else {	// default P - centred on whole page
		$x = ($this->w/2) - ($w/2);
		$y = ($this->h/2) - ($h/2);
	  }
/*-- IMAGES-WMF --*/
	  if ($info['type']=='wmf') { 
		$sx = $w*_MPDFK / $info['w'];
		$sy = -$h*_MPDFK / $info['h'];
		$outstring = sprintf('q %.3F 0 0 %.3F %.3F %.3F cm /FO%d Do Q', $sx, $sy, $x*_MPDFK-$sx*$info['x'], (($this->h-$y)*_MPDFK)-$sy*$info['y'], $info['i']);
	  }
	  else  
/*-- END IMAGES-WMF --*/
	  if ($info['type']=='svg') { 
		$sx = $w*_MPDFK / $info['w'];
		$sy = -$h*_MPDFK / $info['h'];
		$outstring = sprintf('q %.3F 0 0 %.3F %.3F %.3F cm /FO%d Do Q', $sx, $sy, $x*_MPDFK-$sx*$info['x'], (($this->h-$y)*_MPDFK)-$sy*$info['y'], $info['i']);
	  }
	  else { 
		$outstring = sprintf("q %.3F 0 0 %.3F %.3F %.3F cm /I%d Do Q",$w*_MPDFK,$h*_MPDFK,$x*_MPDFK,($this->h-($y+$h))*_MPDFK,$info['i']);
	  }

	  if ($this->watermarkImgBehind) { 
		$outstring = $this->watermarkImgAlpha . "\n" . $outstring . "\n" . $this->SetAlpha(1, 'Normal', true) . "\n";
		$this->pages[$this->page] = preg_replace('/(___BACKGROUND___PATTERNS'.date('jY').')/', "\n".$outstring."\n".'\\1', $this->pages[$this->page]);
	  }
	  else { $this->_out($outstring); }

	  return 0;
	}	// end of IF watermark
/*-- END WATERMARK --*/

	if ($constrain) {
	  // Automatically resize to maximum dimensions of page if too large
	  if (isset($this->blk[$this->blklvl]['inner_width']) && $this->blk[$this->blklvl]['inner_width']) { $maxw = $this->blk[$this->blklvl]['inner_width']; }
	  else { $maxw = $this->pgwidth; }
	  if ($w > $maxw) {
		$w = $maxw;
		$h=abs($w*$info['h']/$info['w']);
	  }
	  if ($h > $this->h - ($this->tMargin + $this->bMargin + 1))  {  // see below - +10 to avoid drawing too close to border of page
   		$h = $this->h - ($this->tMargin + $this->bMargin + 1) ;
		if ($this->fullImageHeight) { $h = $this->fullImageHeight; }
		$w=abs($h*$info['w']/$info['h']);
	  }


	  //Avoid drawing out of the paper(exceeding width limits).
	  //if ( ($x + $w) > $this->fw ) {
	  if ( ($x + $w) > $this->w ) {
		$x = $this->lMargin;
		$y += 5;
	  }

	  $changedpage = false;
	  $oldcolumn = $this->CurrCol;
	  //Avoid drawing out of the page.
	  if($y+$h>$this->PageBreakTrigger and !$this->InFooter and $this->AcceptPageBreak()) {
		$this->AddPage($this->CurOrientation);
		// Added to correct for OddEven Margins
		$x=$x +$this->MarginCorrection;
		$y = $tMargin + $this->margin_header;
		$changedpage = true;
	  }
/*-- COLUMNS --*/
	  // COLS
	  // COLUMN CHANGE
	  if ($this->CurrCol != $oldcolumn) {
		$y = $this->y0;
		$x += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);
		$this->x += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);
	  }
/*-- END COLUMNS --*/
	}	// end of IF constrain

/*-- IMAGES-WMF --*/
	if ($info['type']=='wmf') { 
		$sx = $w*_MPDFK / $info['w'];
		$sy = -$h*_MPDFK / $info['h'];
		$outstring = sprintf('q %.3F 0 0 %.3F %.3F %.3F cm /FO%d Do Q', $sx, $sy, $x*_MPDFK-$sx*$info['x'], (($this->h-$y)*_MPDFK)-$sy*$info['y'], $info['i']);
	}
	else  
/*-- END IMAGES-WMF --*/
	if ($info['type']=='svg') { 
		$sx = $w*_MPDFK / $info['w'];
		$sy = -$h*_MPDFK / $info['h'];
		$outstring = sprintf('q %.3F 0 0 %.3F %.3F %.3F cm /FO%d Do Q', $sx, $sy, $x*_MPDFK-$sx*$info['x'], (($this->h-$y)*_MPDFK)-$sy*$info['y'], $info['i']);
	}
	else { 
		$outstring = sprintf("q %.3F 0 0 %.3F %.3F %.3F cm /I%d Do Q",$w*_MPDFK,$h*_MPDFK,$x*_MPDFK,($this->h-($y+$h))*_MPDFK,$info['i']);
	}

	if($paint) {
		$this->_out($outstring);
		if($link) $this->Link($x,$y,$w,$h,$link);

		// Avoid writing text on top of the image. // THIS WAS OUTSIDE THE if ($paint) bit!!!!!!!!!!!!!!!!
		$this->y = $y + $h;
	}

	//Return width-height array
	$sizesarray['WIDTH'] = $w;
	$sizesarray['HEIGHT'] = $h;
	$sizesarray['X'] = $x; //Position before painting image
	$sizesarray['Y'] = $y; //Position before painting image
	$sizesarray['OUTPUT'] = $outstring;

	$sizesarray['IMAGE_ID'] = $info['i'];
	$sizesarray['itype'] = $info['type'];
	$sizesarray['set-dpi'] = $info['set-dpi'];
	return $sizesarray;
}



//=============================================================
//=============================================================
//=============================================================
//=============================================================
//=============================================================
/*-- HTML-CSS --*/

function _getObjAttr($t) {
	$c = explode("\xbb\xa4\xac",$t,2);
	$c = explode(",",$c[1],2);
	foreach($c as $v) {
		$v = explode("=",$v,2);
		$sp[$v[0]] = $v[1];
	}
	return (unserialize($sp['objattr']));
}


function inlineObject($type,$x,$y,$objattr,$Lmargin,$widthUsed,$maxWidth,$lineHeight,$paint=false,$is_table=false)
{
   if ($is_table) { $k = $this->shrin_k; } else { $k = 1; }

   // NB $x is only used when paint=true
	// Lmargin not used
   $w = 0; 
   if (isset($objattr['width'])) { $w = $objattr['width']/$k; }
   $h = 0;
   if (isset($objattr['height'])) { $h = abs($objattr['height']/$k); }
   $widthLeft = $maxWidth - $widthUsed;
   $maxHeight = $this->h - ($this->tMargin + $this->bMargin + 10) ;
   if ($this->fullImageHeight) { $maxHeight = $this->fullImageHeight; }
   // For Images
   if (isset($objattr['border_left'])) {
	$extraWidth = ($objattr['border_left']['w'] + $objattr['border_right']['w'] + $objattr['margin_left']+ $objattr['margin_right'])/$k;
	$extraHeight = ($objattr['border_top']['w'] + $objattr['border_bottom']['w'] + $objattr['margin_top']+ $objattr['margin_bottom'])/$k;

	if ($type == 'image' || $type == 'barcode' || $type == 'textcircle') {
		$extraWidth += ($objattr['padding_left'] + $objattr['padding_right'])/$k;
		$extraHeight += ($objattr['padding_top'] + $objattr['padding_bottom'])/$k;
	}
   }

   if (!isset($objattr['vertical-align'])) { $objattr['vertical-align'] = 'M'; }

   if ($type == 'image' || (isset($objattr['subtype']) && $objattr['subtype'] == 'IMAGE')) {
    if (isset($objattr['itype']) && ($objattr['itype'] == 'wmf' || $objattr['itype'] == 'svg')) {
	$file = $objattr['file'];
 	$info=$this->formobjects[$file];
    }
    else if (isset($objattr['file'])) {
	$file = $objattr['file'];
	$info=$this->images[$file];
    }
   }
    if ($type == 'annot' || $type == 'bookmark' || $type == 'indexentry' || $type == 'toc') {
	$w = 0.00001;
	$h = 0.00001;
   }

   // TEST whether need to skipline
   if (!$paint) {
	if ($type == 'hr') {	// always force new line
		if (($y + $h + $lineHeight > $this->PageBreakTrigger) && !$this->InFooter && !$is_table) { return array(-2, $w ,$h ); } // New page + new line
		else { return array(1, $w ,$h ); } // new line
	}
	else {
		if ($widthUsed > 0 && $w > $widthLeft && (!$is_table || $type != 'image')) { 	// New line needed
			if (($y + $h + $lineHeight > $this->PageBreakTrigger) && !$this->InFooter) { return array(-2,$w ,$h ); } // New page + new line
			return array(1,$w ,$h ); // new line
		}
		else if ($widthUsed > 0 && $w > $widthLeft && $is_table) { 	// New line needed in TABLE
			return array(1,$w ,$h ); // new line
		}
		// Will fit on line but NEW PAGE REQUIRED
		else if (($y + $h > $this->PageBreakTrigger) && !$this->InFooter && !$is_table) { return array(-1,$w ,$h ); }
		else { return array(0,$w ,$h ); }
	}
   }

   if ($type == 'annot' || $type == 'bookmark' || $type == 'indexentry' || $type == 'toc') {
	$w = 0.00001;
	$h = 0.00001;
	$objattr['BORDER-WIDTH'] = 0;
	$objattr['BORDER-HEIGHT'] = 0;
	$objattr['BORDER-X'] = $x;
	$objattr['BORDER-Y'] = $y;
	$objattr['INNER-WIDTH'] = 0;
	$objattr['INNER-HEIGHT'] = 0;
	$objattr['INNER-X'] = $x;
	$objattr['INNER-Y'] = $y;
  }

  if ($type == 'image') {
	// Automatically resize to width remaining
	if ($w > $widthLeft  && !$is_table) {
		$w = $widthLeft ;
		$h=abs($w*$info['h']/$info['w']);
	}
	$img_w = $w - $extraWidth ;
	$img_h = $h - $extraHeight ;

	$objattr['BORDER-WIDTH'] = $img_w + $objattr['padding_left']/$k + $objattr['padding_right']/$k + (($objattr['border_left']['w']/$k + $objattr['border_right']['w']/$k)/2) ;
	$objattr['BORDER-HEIGHT'] = $img_h + $objattr['padding_top']/$k + $objattr['padding_bottom']/$k + (($objattr['border_top']['w']/$k + $objattr['border_bottom']['w']/$k)/2) ;
	$objattr['BORDER-X'] = $x + $objattr['margin_left']/$k + (($objattr['border_left']['w']/$k)/2) ;
	$objattr['BORDER-Y'] = $y + $objattr['margin_top']/$k + (($objattr['border_top']['w']/$k)/2) ;
	$objattr['INNER-WIDTH'] = $img_w;
	$objattr['INNER-HEIGHT'] = $img_h;
	$objattr['INNER-X'] = $x + $objattr['padding_left']/$k + $objattr['margin_left']/$k + ($objattr['border_left']['w']/$k);
	$objattr['INNER-Y'] = $y + $objattr['padding_top']/$k + $objattr['margin_top']/$k + ($objattr['border_top']['w']/$k) ;
	$objattr['ID'] = $info['i'];
   }

   if ($type == 'input' && $objattr['subtype'] == 'IMAGE') { 
	$img_w = $w - $extraWidth ;
	$img_h = $h - $extraHeight ;
	$objattr['BORDER-WIDTH'] = $img_w + (($objattr['border_left']['w']/$k + $objattr['border_right']['w']/$k)/2) ;
	$objattr['BORDER-HEIGHT'] = $img_h + (($objattr['border_top']['w']/$k + $objattr['border_bottom']['w']/$k)/2) ;
	$objattr['BORDER-X'] = $x + $objattr['margin_left']/$k + (($objattr['border_left']['w']/$k)/2) ;
	$objattr['BORDER-Y'] = $y + $objattr['margin_top']/$k + (($objattr['border_top']['w']/$k)/2) ;
	$objattr['INNER-WIDTH'] = $img_w;
	$objattr['INNER-HEIGHT'] = $img_h;
	$objattr['INNER-X'] = $x + $objattr['margin_left']/$k + ($objattr['border_left']['w']/$k);
	$objattr['INNER-Y'] = $y + $objattr['margin_top']/$k + ($objattr['border_top']['w']/$k) ;
	$objattr['ID'] = $info['i'];
   }

  if ($type == 'barcode' || $type == 'textcircle') {
	$b_w = $w - $extraWidth ;
	$b_h = $h - $extraHeight ;
	$objattr['BORDER-WIDTH'] = $b_w + $objattr['padding_left']/$k + $objattr['padding_right']/$k + (($objattr['border_left']['w']/$k + $objattr['border_right']['w']/$k)/2) ;
	$objattr['BORDER-HEIGHT'] = $b_h + $objattr['padding_top']/$k + $objattr['padding_bottom']/$k + (($objattr['border_top']['w']/$k + $objattr['border_bottom']['w']/$k)/2) ;
	$objattr['BORDER-X'] = $x + $objattr['margin_left']/$k + (($objattr['border_left']['w']/$k)/2) ;
	$objattr['BORDER-Y'] = $y + $objattr['margin_top']/$k + (($objattr['border_top']['w']/$k)/2) ;
	$objattr['INNER-X'] = $x + $objattr['padding_left']/$k + $objattr['margin_left']/$k + ($objattr['border_left']['w']/$k);
	$objattr['INNER-Y'] = $y + $objattr['padding_top']/$k + $objattr['margin_top']/$k + ($objattr['border_top']['w']/$k) ;
	$objattr['INNER-WIDTH'] = $b_w;
	$objattr['INNER-HEIGHT'] = $b_h;
   }


   if ($type == 'textarea') {
	// Automatically resize to width remaining
	if ($w > $widthLeft && !$is_table) {
		$w = $widthLeft ;
	}
	if (($y + $h > $this->PageBreakTrigger) && !$this->InFooter) {
		$h=$this->h - $y - $this->bMargin;
	}
   }

   if ($type == 'hr') {
	if ($is_table) { 
		$objattr['INNER-WIDTH'] = $maxWidth * $objattr['W-PERCENT']/100; 
		$objattr['width'] = $objattr['INNER-WIDTH']; 
		$w = $maxWidth;
	}
	else { 
		if ($w>$maxWidth) { $w = $maxWidth; }
		$objattr['INNER-WIDTH'] = $w; 
		$w = $maxWidth;
	}
  }



   if (($type == 'select') || ($type == 'input' && ($objattr['subtype'] == 'TEXT' || $objattr['subtype'] == 'PASSWORD'))) {
	// Automatically resize to width remaining
	if ($w > $widthLeft && !$is_table) {
		$w = $widthLeft;
	}
   }

   if ($type == 'textarea' || $type == 'select' || $type == 'input') {
	if (isset($objattr['fontsize'])) $objattr['fontsize'] /= $k;
	if (isset($objattr['linewidth'])) $objattr['linewidth'] /= $k;
   }

   if (!isset($objattr['BORDER-Y'])) { $objattr['BORDER-Y'] = 0; }
   if (!isset($objattr['BORDER-X'])) { $objattr['BORDER-X'] = 0; }
   if (!isset($objattr['INNER-Y'])) { $objattr['INNER-Y'] = 0; }
   if (!isset($objattr['INNER-X'])) { $objattr['INNER-X'] = 0; }

   //Return width-height array
   $objattr['OUTER-WIDTH'] = $w;
   $objattr['OUTER-HEIGHT'] = $h;
   $objattr['OUTER-X'] = $x;
   $objattr['OUTER-Y'] = $y;
   return $objattr;
}

/*-- END HTML-CSS --*/

//=============================================================
//=============================================================
//=============================================================
//=============================================================
//=============================================================

function SetLineJoin($mode=0)
{
	$s=sprintf('%d j',$mode);
	if($this->page>0 && ((isset($this->pageoutput[$this->page]['LineJoin']) && $this->pageoutput[$this->page]['LineJoin'] != $s) || !isset($this->pageoutput[$this->page]['LineJoin']) || $this->keep_block_together)) { $this->_out($s); }
	$this->pageoutput[$this->page]['LineJoin'] = $s;

}
function SetLineCap($mode=2) {
	$s=sprintf('%d J',$mode);
	if($this->page>0 && ((isset($this->pageoutput[$this->page]['LineCap']) && $this->pageoutput[$this->page]['LineCap'] != $s) || !isset($this->pageoutput[$this->page]['LineCap']) || $this->keep_block_together)) { $this->_out($s); }
	$this->pageoutput[$this->page]['LineCap'] = $s;

}

function SetDash($black=false,$white=false)
{
        if($black and $white) $s=sprintf('[%.3F %.3F] 0 d',$black*_MPDFK,$white*_MPDFK);
        else $s='[] 0 d';
	if($this->page>0 && ((isset($this->pageoutput[$this->page]['Dash']) && $this->pageoutput[$this->page]['Dash'] != $s) || !isset($this->pageoutput[$this->page]['Dash']) || $this->keep_block_together)) { $this->_out($s); }
	$this->pageoutput[$this->page]['Dash'] = $s;

}

function SetDisplayPreferences($preferences) {
	// String containing any or none of /HideMenubar/HideToolbar/HideWindowUI/DisplayDocTitle/CenterWindow/FitWindow
    $this->DisplayPreferences .= $preferences;
}


function Ln($h='',$collapsible=0)
{
// Added collapsible to allow collapsible top-margin on new page
	//Line feed; default value is last cell height
	$this->x = $this->lMargin + $this->blk[$this->blklvl]['outer_left_margin'];
	if ($collapsible && ($this->y==$this->tMargin) && (!$this->ColActive)) { $h = 0; }
	if(is_string($h)) $this->y+=$this->lasth;
	else $this->y+=$h;
}

/*-- HTML-CSS --*/
// $state = 0 normal; 1 top; 2 bottom; 3 top and bottom
function DivLn($h,$level=-3,$move_y=true,$collapsible=false,$state=0) {
  // this->x is returned as it was
  // adds lines (y) where DIV bgcolors are filled in
  // allows .00001 as nominal height used for bookmarks/annotations etc.
  if ($collapsible && (sprintf("%0.4f", $this->y)==sprintf("%0.4f", $this->tMargin)) && (!$this->ColActive)) { return; }
  if ($collapsible && (sprintf("%0.4f", $this->y)==sprintf("%0.4f", $this->y0)) && ($this->ColActive) && $this->CurrCol == 0) { return; }	// *COLUMNS*

	// Still use this method if columns or page-break-inside: avoid, as it allows repositioning later
	// otherwise, now uses PaintDivBB()
  if (!$this->ColActive && !$this->keep_block_together && !$this->kwt) {
	if ($move_y && !$this->ColActive) { $this->y += $h; }
	return; 
  }

  if ($level == -3) { $level = $this->blklvl; }
  $firstblockfill = $this->GetFirstBlockFill();
  if ($firstblockfill && $this->blklvl > 0 && $this->blklvl >= $firstblockfill) {
	$last_x = 0;
	$last_w = 0;
	$last_fc = $this->FillColor;
	$bak_x = $this->x;
	$bak_h = $this->divheight;
	$this->divheight = 0;	// Temporarily turn off divheight - as Cell() uses it to check for PageBreak
	for ($blvl=$firstblockfill;$blvl<=$level;$blvl++) {
		$this->SetBlockFill($blvl);
		$this->x = $this->lMargin + $this->blk[$blvl]['outer_left_margin'];
		if ($last_x != $this->lMargin + $this->blk[$blvl]['outer_left_margin'] || $last_w != $this->blk[$blvl]['width'] || $last_fc != $this->FillColor || $this->blk[$blvl]['border_top']['s'] || $this->blk[$blvl]['border_bottom']['s'] || $this->blk[$blvl]['border_left']['s'] || $this->blk[$blvl]['border_right']['s']) {	// mPDF 5.6.55
			$x = $this->x;
			$this->Cell( ($this->blk[$blvl]['width']), $h, '', '', 0, '', 1);
			if (!$this->keep_block_together && !$this->writingHTMLheader && !$this->writingHTMLfooter) {
				$this->x = $x;
				// $state = 0 normal; 1 top; 2 bottom; 3 top and bottom
				if ($blvl == $this->blklvl) { $this->PaintDivLnBorder($state,$blvl,$h); }
				else { $this->PaintDivLnBorder(0,$blvl,$h); }
			}
		}
		$last_x = $this->lMargin + $this->blk[$blvl]['outer_left_margin'];
		$last_w = $this->blk[$blvl]['width'];
		$last_fc = $this->FillColor;
	}
	// Reset current block fill
	if (isset($this->blk[$this->blklvl]['bgcolorarray'])) { 
		$bcor = $this->blk[$this->blklvl]['bgcolorarray'];
		$this->SetFColor($bcor);
	}
	$this->x = $bak_x;
	$this->divheight = $bak_h;
  }
  if ($move_y) { $this->y += $h; }
}
/*-- END HTML-CSS --*/


function SetX($x)
{
	//Set x position
	if($x >= 0)	$this->x=$x;
	else $this->x = $this->w + $x;
}

function SetY($y)
{
	//Set y position and reset x
	$this->x=$this->lMargin;
	if($y>=0)
		$this->y=$y;
	else
		$this->y=$this->h+$y;
}

function SetXY($x,$y)
{
	//Set x and y positions
	$this->SetY($y);
	$this->SetX($x);
}


function Output($name='',$dest='')
{
	//Output PDF to some destination
	if ($this->showStats) {
		echo '<div>Generated in '.sprintf('%.2F',(microtime(true) - $this->time0)).' seconds</div>';
	}
	//Finish document if necessary
	if ($this->progressBar) { $this->UpdateProgressBar(1,'100','Finished'); }	// *PROGRESS-BAR*
	if($this->state < 3) $this->Close();
	if ($this->progressBar) { $this->UpdateProgressBar(2,'100','Finished'); }	// *PROGRESS-BAR*
	// fn. error_get_last is only in PHP>=5.2
	if ($this->debug && function_exists('error_get_last') && error_get_last()) {
	   $e = error_get_last(); 
	   if (($e['type'] < 2048 && $e['type'] != 8) || (intval($e['type']) & intval(ini_get("error_reporting")))) {
		echo "<p>Error message detected - PDF file generation aborted.</p>"; 
		echo $e['message'].'<br />';
		echo 'File: '.$e['file'].'<br />';
		echo 'Line: '.$e['line'].'<br />';
		exit; 
	   }
	}


	if (($this->PDFA || $this->PDFX) && $this->encrypted) { $this->Error("PDFA1-b or PDFX/1-a does not permit encryption of documents."); }
	if (count($this->PDFAXwarnings) && (($this->PDFA && !$this->PDFAauto) || ($this->PDFX && !$this->PDFXauto))) {
		if ($this->PDFA) {
			echo '<div>WARNING - This file could not be generated as it stands as a PDFA1-b compliant file.</div>';
			echo '<div>These issues can be automatically fixed by mPDF using <i>$mpdf-&gt;PDFAauto=true;</i></div>';
			echo '<div>Action that mPDF will take to automatically force PDFA1-b compliance are shown in brackets.</div>';
		}
		else {
			echo '<div>WARNING - This file could not be generated as it stands as a PDFX/1-a compliant file.</div>';
			echo '<div>These issues can be automatically fixed by mPDF using <i>$mpdf-&gt;PDFXauto=true;</i></div>';
			echo '<div>Action that mPDF will take to automatically force PDFX/1-a compliance are shown in brackets.</div>';
		}
		echo '<div>Warning(s) generated:</div><ul>';
		$this->PDFAXwarnings = array_unique($this->PDFAXwarnings);
		foreach($this->PDFAXwarnings AS $w) {
			echo '<li>'.$w.'</li>';
		}
		echo '</ul>';
		exit;
	}

	if ($this->showStats) {
		echo '<div>Compiled in '.sprintf('%.2F',(microtime(true) - $this->time0)).' seconds (total)</div>';
		echo '<div>Peak Memory usage '.number_format((memory_get_peak_usage(true)/(1024*1024)),2).' MB</div>';
		echo '<div>PDF file size '.number_format((strlen($this->buffer)/1024)).' kB</div>';
		echo '<div>Number of fonts '.count($this->fonts).'</div>';
		exit;
	}


	if(is_bool($dest)) $dest=$dest ? 'D' : 'F';
	$dest=strtoupper($dest);
	if($dest=='') {
		if($name=='') {
			$name='mpdf.pdf';
			$dest='I';
		}
		else { $dest='F'; }
	}

/*-- PROGRESS-BAR --*/
	if ($this->progressBar && ($dest=='D' || $dest=='I')) {
		if($name=='') { $name='mpdf.pdf'; }
		$tempfile = '_tempPDF'.RAND(1,10000);
		//Save to local file
		$f=fopen(_MPDF_TEMP_PATH.$tempfile.'.pdf','wb');
		if(!$f) $this->Error('Unable to create temporary output file: '.$tempfile.'.pdf');
		fwrite($f,$this->buffer,strlen($this->buffer));
		fclose($f);
		$this->UpdateProgressBar(3,'','Finished');

		echo '<script type="text/javascript">

		var form = document.createElement("form");
		form.setAttribute("method", "post");
		form.setAttribute("action", "'._MPDF_URI.'includes/out.php");

		var hiddenField = document.createElement("input");
		hiddenField.setAttribute("type", "hidden");
		hiddenField.setAttribute("name", "filename");
		hiddenField.setAttribute("value", "'.$tempfile.'");
		form.appendChild(hiddenField);

		var hiddenField = document.createElement("input");
		hiddenField.setAttribute("type", "hidden");
		hiddenField.setAttribute("name", "dest");
		hiddenField.setAttribute("value", "'.$dest.'");
		form.appendChild(hiddenField);

		var hiddenField = document.createElement("input");
		hiddenField.setAttribute("type", "hidden");
		hiddenField.setAttribute("name", "opname");
		hiddenField.setAttribute("value", "'.$name.'");
		form.appendChild(hiddenField);

		var hiddenField = document.createElement("input");
		hiddenField.setAttribute("type", "hidden");
		hiddenField.setAttribute("name", "path");
		hiddenField.setAttribute("value", "'.urlencode(_MPDF_TEMP_PATH).'");
		form.appendChild(hiddenField);

		document.body.appendChild(form); 
		form.submit();

      	</script>
		</div>
		</body>
		</html>';
		exit;
	}
	else {
		if ($this->progressBar) { $this->UpdateProgressBar(3,'','Finished'); }
/*-- END PROGRESS-BAR --*/

		switch($dest) {
		   case 'I':
			if ($this->debug && !$this->allow_output_buffering && ob_get_contents()) { echo "<p>Output has already been sent from the script - PDF file generation aborted.</p>"; exit; }
			//Send to standard output
			if(PHP_SAPI!='cli') {
				//We send to a browser
				header('Content-Type: application/pdf');
				if(headers_sent())
					$this->Error('Some data has already been output to browser, can\'t send PDF file');
				if (!isset($_SERVER['HTTP_ACCEPT_ENCODING']) OR empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
					// don't use length if server using compression
					header('Content-Length: '.strlen($this->buffer));
				}
				header('Content-disposition: inline; filename="'.$name.'"');
				header('Cache-Control: public, must-revalidate, max-age=0'); 
				header('Pragma: public');
				header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); 
				header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
			}
			echo $this->buffer;
			break;
		   case 'D':
			//Download file
			header('Content-Description: File Transfer');
			if (headers_sent())
				$this->Error('Some data has already been output to browser, can\'t send PDF file');
			header('Content-Transfer-Encoding: binary');
			header('Cache-Control: public, must-revalidate, max-age=0');
			header('Pragma: public');
			header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
			header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
			header('Content-Type: application/force-download');
			header('Content-Type: application/octet-stream', false);
			header('Content-Type: application/download', false);
			header('Content-Type: application/pdf', false);
			if (!isset($_SERVER['HTTP_ACCEPT_ENCODING']) OR empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
				// don't use length if server using compression
				header('Content-Length: '.strlen($this->buffer));
			}
			header('Content-disposition: attachment; filename="'.$name.'"');
 			echo $this->buffer;
			break;
		   case 'F':
			//Save to local file
			$f=fopen($name,'wb');
			if(!$f) $this->Error('Unable to create output file: '.$name);
			fwrite($f,$this->buffer,strlen($this->buffer));
			fclose($f);
			break;
		   case 'S':
			//Return as a string
			return $this->buffer;
		   default:
			$this->Error('Incorrect output destination: '.$dest);
		}

	}	// *PROGRESS-BAR*
	//======================================================================================================
	// DELETE OLD TMP FILES - Housekeeping
	// Delete any files in tmp/ directory that are >1 hrs old
		$interval = 3600;
		if ($handle = opendir(preg_replace('/\/$/','',_MPDF_TEMP_PATH))) {
		   while (false !== ($file = readdir($handle))) { 
			if (!is_dir($file) && ((filemtime(_MPDF_TEMP_PATH.$file)+$interval) < time()) && ($file != "..") && ($file != ".")) { // mPDF 5.4.19
				unlink(_MPDF_TEMP_PATH.$file); 
			}
		   }
		   closedir($handle); 
		}
	//==============================================================================================================

	return '';
}


// *****************************************************************************
//                                                                             *
//                             Protected methods                               *
//                                                                             *
// *****************************************************************************
function _dochecks()
{
	//Check for locale-related bug
	if(1.1==1)
		$this->Error('Don\'t alter the locale before including mPDF');
	//Check for decimal separator
	if(sprintf('%.1f',1.0)!='1.0')
		setlocale(LC_NUMERIC,'C');
	// mPDF 5.4.11
	$mqr=ini_get("magic_quotes_runtime");
	if ($mqr) { $this->Error('mPDF requires magic_quotes_runtime to be turned off e.g. by using ini_set("magic_quotes_runtime", 0);'); }
}

function _begindoc()
{
	//Start document
	$this->state=1;
	$this->_out('%PDF-'.$this->pdf_version);
	$this->_out('%'.chr(226).chr(227).chr(207).chr(211));	// 4 chars > 128 to show binary file
}


/*-- HTMLHEADERS-FOOTERS --*/
function _puthtmlheaders() {
	$this->state=2;
	$nb=$this->page;
	for($n=1;$n<=$nb;$n++) {
	  if ($this->mirrorMargins && $n%2==0) { $OE = 'E'; }	// EVEN
	  else { $OE = 'O'; }
	  $this->page = $n;
	  if (isset($this->saveHTMLHeader[$n][$OE])) {
		$html = $this->saveHTMLHeader[$n][$OE]['html'];
		$this->lMargin = $this->saveHTMLHeader[$n][$OE]['ml'];
		$this->rMargin = $this->saveHTMLHeader[$n][$OE]['mr'];
		$this->tMargin = $this->saveHTMLHeader[$n][$OE]['mh'];
		$this->bMargin = $this->saveHTMLHeader[$n][$OE]['mf'];
		$this->margin_header = $this->saveHTMLHeader[$n][$OE]['mh'];
		$this->margin_footer = $this->saveHTMLHeader[$n][$OE]['mf'];
		$this->w = $this->saveHTMLHeader[$n][$OE]['pw'];
		$this->h = $this->saveHTMLHeader[$n][$OE]['ph'];
		$rotate = (isset($this->saveHTMLHeader[$n][$OE]['rotate']) ? $this->saveHTMLHeader[$n][$OE]['rotate'] : null);
		$this->Reset();
		$this->pageoutput[$n] = array();
		$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
		$this->x = $this->lMargin;
		$this->y = $this->margin_header;
		// mPDF 5.6.47
		$pn = $this->docPageNum($n);
		if ($pn)
			$pnstr = $this->pagenumPrefix.$pn.$this->pagenumSuffix;
		else { $pnstr = ''; }
		$html = str_replace('{PAGENO}',$pnstr,$html);
		$pnt = $this->docPageNumTotal($n);
		if ($pnt)
			$pntstr = $this->nbpgPrefix.$pnt.$this->nbpgSuffix;
		else { $pntstr = ''; }
		$html = str_replace($this->aliasNbPgGp,$pntstr,$html );	// {nbpg}
		$html = str_replace($this->aliasNbPg,$nb,$html );	// {nb}
		$html = preg_replace('/\{DATE\s+(.*?)\}/e',"date('\\1')",$html );

		$this->HTMLheaderPageLinks = array();
		$this->HTMLheaderPageAnnots = array();
		$this->HTMLheaderPageForms = array();
		$this->pageBackgrounds = array();

		$this->writingHTMLheader = true;
		$this->WriteHTML($html , 4);	// parameter 4 saves output to $this->headerbuffer
		$this->writingHTMLheader = false;
		$this->Reset();
		$this->pageoutput[$n] = array();

		$s = $this->PrintPageBackgrounds();
		$this->headerbuffer = $s . $this->headerbuffer;
		$os = '';
		if ($rotate) {
			$os .= sprintf('q 0 -1 1 0 0 %.3F cm ',($this->w*_MPDFK));
		}
		$os .= $this->headerbuffer ;
		if ($rotate) {
			$os .= ' Q' . "\n";
		}

		// Writes over the page background but behind any other output on page
		$os = preg_replace('/\\\\/','\\\\\\\\',$os);
		$this->pages[$n] = preg_replace('/(___HEADER___MARKER'.date('jY').')/', "\n".$os."\n".'\\1', $this->pages[$n]);

		$lks = $this->HTMLheaderPageLinks; 
		foreach($lks AS $lk) {
			if ($rotate) {
				$lw = $lk[2];
				$lh = $lk[3];
				$lk[2] = $lh;
				$lk[3] = $lw;	// swap width and height
				$ax = $lk[0]/_MPDFK;
				$ay = $lk[1]/_MPDFK;
				$bx = $ay-($lh/_MPDFK);
				$by = $this->w-$ax;
				$lk[0] = $bx*_MPDFK;
				$lk[1] = ($this->h-$by)*_MPDFK - $lw;
			}
			$this->PageLinks[$n][]=$lk;
		}
/*-- FORMS --*/
		foreach($this->HTMLheaderPageForms AS $f) {
			$this->form->forms[$f['n']] = $f;
		}
/*-- END FORMS --*/


	  }
	  if (isset($this->saveHTMLFooter[$n][$OE])) {
		$html = $this->saveHTMLFooter[$this->page][$OE]['html'];
		$this->lMargin = $this->saveHTMLFooter[$n][$OE]['ml'];
		$this->rMargin = $this->saveHTMLFooter[$n][$OE]['mr'];
		$this->tMargin = $this->saveHTMLFooter[$n][$OE]['mh'];
		$this->bMargin = $this->saveHTMLFooter[$n][$OE]['mf'];
		$this->margin_header = $this->saveHTMLFooter[$n][$OE]['mh'];
		$this->margin_footer = $this->saveHTMLFooter[$n][$OE]['mf'];
		$this->w = $this->saveHTMLFooter[$n][$OE]['pw'];
		$this->h = $this->saveHTMLFooter[$n][$OE]['ph'];
		$rotate = (isset($this->saveHTMLFooter[$n][$OE]['rotate']) ? $this->saveHTMLFooter[$n][$OE]['rotate'] : null);
		$this->Reset();
		$this->pageoutput[$n] = array();
		$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
		$this->x = $this->lMargin;
		$top_y = $this->y = $this->h - $this->margin_footer;

		// if bottom-margin==0, corrects to avoid division by zero
		if ($this->y == $this->h) { $top_y = $this->y = ($this->h - 0.1); }
		// mPDF 5.6.47
		$pn = $this->docPageNum($n);
		if ($pn)
			$pnstr = $this->pagenumPrefix.$pn.$this->pagenumSuffix;
		else { $pnstr = ''; }
		$html = str_replace('{PAGENO}',$pnstr,$html);
		$pnt = $this->docPageNumTotal($n);
		if ($pnt)
			$pntstr = $this->nbpgPrefix.$pnt.$this->nbpgSuffix;
		else { $pntstr = ''; }
		$html = str_replace($this->aliasNbPgGp,$pntstr,$html );	// {nbpg}
		$html = str_replace($this->aliasNbPg,$nb,$html );	// {nb}
		$html = preg_replace('/\{DATE\s+(.*?)\}/e',"date('\\1')",$html );


		$this->HTMLheaderPageLinks = array();
		$this->HTMLheaderPageAnnots = array();
		$this->HTMLheaderPageForms = array();
		$this->pageBackgrounds = array();

		$this->writingHTMLfooter = true;
		$this->InFooter = true;
		$this->WriteHTML($html , 4);	// parameter 4 saves output to $this->headerbuffer
		$this->writingHTMLfooter = false;
		$this->InFooter = false;
		$this->Reset();
		$this->pageoutput[$n] = array();

		$fheight = $this->y - $top_y;
		$adj = -$fheight;

		$s = $this->PrintPageBackgrounds(-$adj);
		$this->headerbuffer = $s . $this->headerbuffer;

		$os = '';
		$os .= $this->StartTransform(true)."\n";
		if ($rotate) {
			$os .= sprintf('q 0 -1 1 0 0 %.3F cm ',($this->w*_MPDFK));
		}
		$os .= $this->transformTranslate(0, $adj, true)."\n";
		$os .= $this->headerbuffer ;
		if ($rotate) {
			$os .= ' Q' . "\n";
		}
		$os .= $this->StopTransform(true)."\n";
		// Writes over the page background but behind any other output on page
		$os = preg_replace('/\\\\/','\\\\\\\\',$os);
		$this->pages[$n] = preg_replace('/(___HEADER___MARKER'.date('jY').')/', "\n".$os."\n".'\\1', $this->pages[$n]);

		$lks = $this->HTMLheaderPageLinks; 
		foreach($lks AS $lk) {
			$lk[1] -= $adj*_MPDFK;
			if ($rotate) {
				$lw = $lk[2];
				$lh = $lk[3];
				$lk[2] = $lh;
				$lk[3] = $lw;	// swap width and height

				$ax = $lk[0]/_MPDFK;
				$ay = $lk[1]/_MPDFK;
				$bx = $ay-($lh/_MPDFK);
				$by = $this->w-$ax;
				$lk[0] = $bx*_MPDFK;
				$lk[1] = ($this->h-$by)*_MPDFK - $lw;
			}
			$this->PageLinks[$n][]=$lk;
		}
/*-- FORMS --*/
		foreach($this->HTMLheaderPageForms AS $f) {
			$f['y'] += $adj;
			$this->form->forms[$f['n']] = $f;
		}
/*-- END FORMS --*/
	  }
	}
	$this->page=$nb;
	$this->state=1;
}
/*-- END HTMLHEADERS-FOOTERS --*/


function _putpages()
{
	$nb=$this->page;
	$filter=($this->compress) ? '/Filter /FlateDecode ' : '';

	if($this->DefOrientation=='P') {
		$defwPt=$this->fwPt;
		$defhPt=$this->fhPt;
	}
	else {
		$defwPt=$this->fhPt;
		$defhPt=$this->fwPt;
	}
	$annotid=(3+2*$nb);

	// Active Forms
	$totaladdnum = 0;
	for($n=1;$n<=$nb;$n++) {
		if (isset($this->PageLinks[$n])) { $totaladdnum += count($this->PageLinks[$n]); }
/*-- ANNOTATIONS --*/
		if (isset($this->PageAnnots[$n])) { 
			foreach ($this->PageAnnots[$n] as $k => $pl) {
				if (!empty($pl['opt']['popup']) || !empty($pl['opt']['file'])) { $totaladdnum += 2 ; }
				else { $totaladdnum++; }
			}
		}
/*-- END ANNOTATIONS --*/

/*-- FORMS --*/
		if ( count($this->form->forms) > 0 ) {
			$this->form->countPageForms($n, $totaladdnum);
		}
/*-- END FORMS --*/
	}
/*-- FORMS --*/
	// Make a note in the radio button group of the obj_id it will have
	$ctr = 0;
	if (count($this->form->form_radio_groups)) {
		foreach($this->form->form_radio_groups AS $name=>$frg) {
			$this->form->form_radio_groups[$name]['obj_id'] = $annotid + $totaladdnum + $ctr;
			$ctr++;
		}
	}
/*-- END FORMS --*/

	// Select unused fonts (usually default font)
	$unused = array();
	foreach($this->fonts as $fk=>$font) {
	   if (!$font['used'] && ($font['type']=='TTF')) { 
		$unused[] = $fk;
	   }
	}


	for($n=1;$n<=$nb;$n++)
	{
		$thispage = $this->pages[$n];
//		unset($this->pages[$n]);	// mPDF 5.6.47
		if(isset($this->OrientationChanges[$n])) { 
			$hPt=$this->pageDim[$n]['w']*_MPDFK;
			$wPt=$this->pageDim[$n]['h']*_MPDFK;
			$owidthPt_LR = $this->pageDim[$n]['outer_width_TB']*_MPDFK;
			$owidthPt_TB = $this->pageDim[$n]['outer_width_LR']*_MPDFK;
		}
		else { 
			$wPt=$this->pageDim[$n]['w']*_MPDFK;
			$hPt=$this->pageDim[$n]['h']*_MPDFK;
			$owidthPt_LR = $this->pageDim[$n]['outer_width_LR']*_MPDFK;
			$owidthPt_TB = $this->pageDim[$n]['outer_width_TB']*_MPDFK;
		}
		// Remove references to unused fonts (usually default font)
		foreach($unused as $fk) {
			if ($this->fonts[$fk]['sip'] || $this->fonts[$fk]['smp']) {
				foreach($this->fonts[$fk]['subsetfontids'] AS $k => $fid) {
						$thispage = preg_replace('/\s\/F'.$fid.' \d[\d.]* Tf\s/is',' ',$thispage); 
				}
			}
			else { 
				$thispage = preg_replace('/\s\/F'.$this->fonts[$fk]['i'].' \d[\d.]* Tf\s/is',' ',$thispage); 
			}
		}
		//Replace number of pages
		if(!empty($this->aliasNbPg)) {
			if (!$this->onlyCoreFonts) { $s1 = $this->UTF8ToUTF16BE($this->aliasNbPg, false); }
			$s2 = $this->aliasNbPg;
			if (!$this->onlyCoreFonts) { $r1 = $this->UTF8ToUTF16BE($nb, false); }
			$r2 = $nb;
			if (preg_match_all('/{mpdfheadernbpg (C|R) ff=(\S*) fs=(\S*) fz=(.*?)}/',$thispage,$m)) {
				for($hi=0;$hi<count($m[0]);$hi++) {
					$pos = $m[1][$hi];
					$hff = $m[2][$hi];
					$hfst = $m[3][$hi];
					$hfsz = $m[4][$hi];
					$this->SetFont($hff,$hfst,$hfsz, false);
					$x1 = $this->GetStringWidth($this->aliasNbPg);
					$x2 = $this->GetStringWidth($nb);
					$xadj = $x1 - $x2;
					if ($pos=='C') { $xadj /= 2; }
					$rep = sprintf(' q 1 0 0 1 %.3F 0 cm ', $xadj*_MPDFK); 
					$thispage = str_replace($m[0][$hi], $rep, $thispage);
				}
			}
			if (!$this->onlyCoreFonts) { $thispage=str_replace($s1,$r1,$thispage); }
			$thispage=str_replace($s2,$r2,$thispage);

			// And now for any SMP/SIP fonts subset using <HH> format
			$r = '';
			$nstr = "$nb";
			for($i=0;$i<strlen($nstr);$i++) {
				$r .= sprintf("%02s", strtoupper(dechex(intval($nstr[$i])+48))); 
			}
			$thispage=str_replace($this->aliasNbPgHex,$r,$thispage);

		}
		//Replace number of pages in group
		if(!empty($this->aliasNbPgGp)) {
			if (!$this->onlyCoreFonts) { $s1 = $this->UTF8ToUTF16BE($this->aliasNbPgGp, false); }
			$s2 = $this->aliasNbPgGp; 
			$nbt = $this->docPageNumTotal($n);
			if (!$this->onlyCoreFonts) { $r1 = $this->UTF8ToUTF16BE($nbt, false); }	
			$r2 = $nbt;
			if (preg_match_all('/{mpdfheadernbpggp (C|R) ff=(\S*) fs=(\S*) fz=(.*?)}/',$thispage,$m)) {
				for($hi=0;$hi<count($m[0]);$hi++) {
					$pos = $m[1][$hi];
					$hff = $m[2][$hi];
					$hfst = $m[3][$hi];
					$hfsz = $m[4][$hi];
					$this->SetFont($hff,$hfst,$hfsz, false);
					$x1 = $this->GetStringWidth($this->aliasNbPgGp);
					$x2 = $this->GetStringWidth($nbt);
					$xadj = $x1 - $x2;
					if ($pos=='C') { $xadj /= 2; }
					$rep = sprintf(' q 1 0 0 1 %.3F 0 cm ', $xadj*_MPDFK); 
					$thispage = str_replace($m[0][$hi], $rep, $thispage);
				}
			}
			if (!$this->onlyCoreFonts) { $thispage=str_replace($s1,$r1,$thispage); }
			$thispage=str_replace($s2,$r2,$thispage);

			// And now for any SMP/SIP fonts subset using <HH> format
			$r = '';
			$nstr = "$nbt";
			for($i=0;$i<strlen($nstr);$i++) {
				$r .= sprintf("%02s", strtoupper(dechex(intval($nstr[$i])+48))); 
			}
			$thispage=str_replace($this->aliasNbPgGpHex,$r,$thispage);

		}
		$thispage = preg_replace('/(\s*___BACKGROUND___PATTERNS'.date('jY').'\s*)/', " ", $thispage);
		$thispage = preg_replace('/(\s*___HEADER___MARKER'.date('jY').'\s*)/', " ", $thispage);
		$thispage = preg_replace('/(\s*___PAGE___START'.date('jY').'\s*)/', " ", $thispage);
		$thispage = preg_replace('/(\s*___TABLE___BACKGROUNDS'.date('jY').'\s*)/', " ", $thispage);

		//Page
		$this->_newobj();
		$this->_out('<</Type /Page');
		$this->_out('/Parent 1 0 R');
		if(isset($this->OrientationChanges[$n])) {
			$this->_out(sprintf('/MediaBox [0 0 %.3F %.3F]',$hPt,$wPt));
			//If BleedBox is defined, it must be larger than the TrimBox, but smaller than the MediaBox
			$bleedMargin = $this->pageDim[$n]['bleedMargin']*_MPDFK;
			if ($bleedMargin && ($owidthPt_TB || $owidthPt_LR)) {
				$x0 = $owidthPt_TB-$bleedMargin;
				$y0 = $owidthPt_LR-$bleedMargin;
				$x1 = $hPt-$owidthPt_TB+$bleedMargin;
				$y1 = $wPt-$owidthPt_LR+$bleedMargin;
				$this->_out(sprintf('/BleedBox [%.3F %.3F %.3F %.3F]', $x0, $y0, $x1, $y1));
			}
			$this->_out(sprintf('/TrimBox [%.3F %.3F %.3F %.3F]', $owidthPt_TB, $owidthPt_LR, ($hPt-$owidthPt_TB), ($wPt-$owidthPt_LR)));	
			if (isset($this->OrientationChanges[$n]) && $this->displayDefaultOrientation) {
				if ($this->DefOrientation=='P') { $this->_out('/Rotate 270'); }
				else { $this->_out('/Rotate 90'); }
			}
		}
		//else if($wPt != $defwPt || $hPt != $defhPt) {
		else {
			$this->_out(sprintf('/MediaBox [0 0 %.3F %.3F]',$wPt,$hPt));
			$bleedMargin = $this->pageDim[$n]['bleedMargin']*_MPDFK;
			if ($bleedMargin && ($owidthPt_TB || $owidthPt_LR)) {
				$x0 = $owidthPt_LR-$bleedMargin;
				$y0 = $owidthPt_TB-$bleedMargin;
				$x1 = $wPt-$owidthPt_LR+$bleedMargin;
				$y1 = $hPt-$owidthPt_TB+$bleedMargin;
				$this->_out(sprintf('/BleedBox [%.3F %.3F %.3F %.3F]', $x0, $y0, $x1, $y1));
			}
			$this->_out(sprintf('/TrimBox [%.3F %.3F %.3F %.3F]', $owidthPt_LR, $owidthPt_TB, ($wPt-$owidthPt_LR), ($hPt-$owidthPt_TB)));	
		}
		$this->_out('/Resources 2 0 R');

		// Important to keep in RGB colorSpace when using transparency
		if (!$this->PDFA && !$this->PDFX) { 
			if ($this->restrictColorSpace == 3)
				$this->_out('/Group << /Type /Group /S /Transparency /CS /DeviceCMYK >> ');
			else if ($this->restrictColorSpace == 1)
				$this->_out('/Group << /Type /Group /S /Transparency /CS /DeviceGray >> ');
			else 
				$this->_out('/Group << /Type /Group /S /Transparency /CS /DeviceRGB >> ');
		}

		$annotsnum = 0;
		if (isset($this->PageLinks[$n])) { $annotsnum += count($this->PageLinks[$n]); }
/*-- ANNOTATIONS --*/
		if (isset($this->PageAnnots[$n])) { 
			foreach ($this->PageAnnots[$n] as $k => $pl) {
				if (!empty($pl['opt']['popup']) || !empty($pl['opt']['file'])) { $annotsnum += 2 ; }
				else { $annotsnum++; }
				$this->PageAnnots[$n][$k]['pageobj'] = $this->n;
			}
		}
/*-- END ANNOTATIONS --*/

/*-- FORMS --*/
		// Active Forms
		$formsnum = 0;
		if ( count($this->form->forms) > 0 ) {
			foreach( $this->form->forms as $val ) {
				if ( $val['page'] == $n )
					$formsnum++;
			}
		}
/*-- END FORMS --*/
		if ($annotsnum || $formsnum) {
			$s = '/Annots [ ';
			for($i=0;$i<$annotsnum;$i++) { 
				$s .= ($annotid + $i) . ' 0 R ';
			} 
			$annotid += $annotsnum;
/*-- FORMS --*/
			if ( count($this->form->forms) > 0 ) {
				$this->form->addFormIds($n, $s, $annotid);
			}
/*-- END FORMS --*/
			$s .= '] ';
			$this->_out($s);
		}

		$this->_out('/Contents '.($this->n+1).' 0 R>>');
		$this->_out('endobj');

		//Page content
		$this->_newobj();
		$p=($this->compress) ? gzcompress($thispage) : $thispage;
		$this->_out('<<'.$filter.'/Length '.strlen($p).'>>');
		$this->_putstream($p);
		$this->_out('endobj');
	}
	$this->_putannots($n);

	//Pages root
	$this->offsets[1]=strlen($this->buffer);
	$this->_out('1 0 obj');
	$this->_out('<</Type /Pages');
	$kids='/Kids [';
	for($i=0;$i<$nb;$i++)
		$kids.=(3+2*$i).' 0 R ';
	$this->_out($kids.']');
	$this->_out('/Count '.$nb);
	$this->_out(sprintf('/MediaBox [0 0 %.3F %.3F]',$defwPt,$defhPt));
	$this->_out('>>');
	$this->_out('endobj');
}


function _putannots($n) {
	$filter=($this->compress) ? '/Filter /FlateDecode ' : '';
	$nb=$this->page;
	for($n=1;$n<=$nb;$n++)
	{
		$annotobjs = array();
		if(isset($this->PageLinks[$n]) || isset($this->PageAnnots[$n]) || count($this->form->forms) > 0 ) {
			$wPt=$this->pageDim[$n]['w']*_MPDFK;
			$hPt=$this->pageDim[$n]['h']*_MPDFK;

			//Links
			if(isset($this->PageLinks[$n])) {
			   foreach($this->PageLinks[$n] as $key => $pl) {
				$this->_newobj();
				$annot='';
				$rect=sprintf('%.3F %.3F %.3F %.3F',$pl[0],$pl[1],$pl[0]+$pl[2],$pl[1]-$pl[3]);
				$annot .= '<</Type /Annot /Subtype /Link /Rect ['.$rect.']';
				$annot .= ' /Contents '.$this->_UTF16BEtextstring($pl[4]);
				$annot .= ' /NM '.$this->_textstring(sprintf('%04u-%04u', $n, $key));
				$annot .= ' /M '.$this->_textstring('D:'.date('YmdHis'));
				$annot .= ' /Border [0 0 0]';
				// Use this (instead of /Border) to specify border around link
		//		$annot .= ' /BS <</W 1';	// Width on points; 0 = no line
		//		$annot .= ' /S /D';		// style - [S]olid, [D]ashed, [B]eveled, [I]nset, [U]nderline
		//		$annot .= ' /D [3 2]';		// Dash array - if dashed
		//		$annot .= ' >>';
		//		$annot .= ' /C [1 0 0]';	// Color RGB

				if ($this->PDFA || $this->PDFX) { $annot .= ' /F 28'; }
				if (strpos($pl[4],'@')===0) {
					$p=substr($pl[4],1);
					//	$h=isset($this->OrientationChanges[$p]) ? $wPt : $hPt;
					$htarg=$this->pageDim[$p]['h']*_MPDFK;
					$annot.=sprintf(' /Dest [%d 0 R /XYZ 0 %.3F null]>>',1+2*$p,$htarg);
				}
				else if(is_string($pl[4])) {
					$annot .= ' /A <</S /URI /URI '.$this->_textstring($pl[4]).'>> >>';
				}
				else {
					$l=$this->links[$pl[4]];
					// may not be set if #link points to non-existent target
					if (isset($this->pageDim[$l[0]]['h'])) { $htarg=$this->pageDim[$l[0]]['h']*_MPDFK; }
					else { $htarg=$this->h*_MPDFK; } // doesn't really matter
					$annot.=sprintf(' /Dest [%d 0 R /XYZ 0 %.3F null]>>',1+2*$l[0],$htarg-$l[1]*_MPDFK);
				}
				$this->_out($annot);
				$this->_out('endobj');
			   }
			}


/*-- ANNOTATIONS --*/
			if(isset($this->PageAnnots[$n])) {
			   foreach ($this->PageAnnots[$n] as $key => $pl) {
				if ($pl['opt']['file']) { $FileAttachment=true; }
				else { $FileAttachment=false; }
				$this->_newobj();
				$annot='';
				$pl['opt'] = array_change_key_case($pl['opt'], CASE_LOWER);
				$x = $pl['x']; 
				if ($this->annotMargin <> 0 || $x==0 || $x<0) {	// Odd page
				   $x = ($wPt/_MPDFK) - $this->annotMargin;
				}
				$w = $h = 0;
				$a = $x * _MPDFK;
				$b = $hPt - ($pl['y']  * _MPDFK);
				$annot .= '<</Type /Annot ';
				if ($FileAttachment) { 
					$annot .= '/Subtype /FileAttachment'; 
					// Need to set a size for FileAttachment icons
					if ($pl['opt']['icon']=='Paperclip') { $w=8.235; $h=20; }	// 7,17
					else if ($pl['opt']['icon']=='Tag') { $w=20; $h=16; }
					else if ($pl['opt']['icon']=='Graph') { $w=20; $h=20; }
					else { $w=14; $h=20; } 	// PushPin 
					$f = $pl['opt']['file'];
					$f = preg_replace('/^.*\//', '', $f);
					$f = preg_replace('/[^a-zA-Z0-9._]/', '', $f);
					$annot .= '/FS <</Type /Filespec /F ('.$f.')';
					$annot .= '/EF <</F '.($this->n+1).' 0 R>>';
					$annot .= '>>';
				}
				else { 
					$annot .= '/Subtype /Text'; 
				}
				$rect = sprintf('%.3F %.3F %.3F %.3F', $a, $b-$h, $a+$w, $b);
				$annot .= '/Rect ['.$rect.']';

				// contents = description of file in free text
				$annot .= ' /Contents '.$this->_UTF16BEtextstring($pl['txt']);
				$annot .= ' /NM '.$this->_textstring(sprintf('%04u-%04u', $n, (2000 + $key)));
				$annot .= ' /M '.$this->_textstring('D:'.date('YmdHis'));
				$annot .= ' /CreationDate '.$this->_textstring('D:'.date('YmdHis'));
				$annot .= ' /Border [0 0 0]';
				if ($this->PDFA || $this->PDFX) { 
					$annot .= ' /F 28'; 
					$annot .= ' /CA 1'; 
				}
				else if ($pl['opt']['ca']>0) { $annot .= ' /CA '.$pl['opt']['ca']; }

				$annotcolor = ' /C [';
				if (isset($pl['opt']['c']) AND $pl['opt']['c']) {
					$col = $pl['opt']['c'];
					if ($col{0}==3 || $col{0}==5) { $annotcolor .= sprintf("%.3F %.3F %.3F", ord($col{1})/255,ord($col{2})/255,ord($col{3})/255); }
					else if ($col{0}==1) { $annotcolor .= sprintf("%.3F", ord($col{1})/255); }
					else if ($col{0}==4 || $col{0}==6) { $annotcolor .= sprintf("%.3F %.3F %.3F %.3F", ord($col{1})/100,ord($col{2})/100,ord($col{3})/100,ord($col{4})/100); }
					else { $annotcolor .= '1 1 0'; }
				}
				else { $annotcolor .= '1 1 0'; }
				$annotcolor .= ']';
				$annot .= $annotcolor;
				// Usually Author
				// Use as Title for fileattachment
				if (isset($pl['opt']['t']) AND is_string($pl['opt']['t'])) {
					$annot .= ' /T '.$this->_UTF16BEtextstring($pl['opt']['t']);
				}
				if ($FileAttachment) {
					$iconsapp = array('Paperclip', 'Graph', 'PushPin', 'Tag'); 
				}
				else { $iconsapp = array('Comment', 'Help', 'Insert', 'Key', 'NewParagraph', 'Note', 'Paragraph'); }
				if (isset($pl['opt']['icon']) AND in_array($pl['opt']['icon'], $iconsapp)) {
					$annot .= ' /Name /'.$pl['opt']['icon'];
				}
				else if ($FileAttachment) { $annot .= ' /Name /PushPin'; }
				else { $annot .= ' /Name /Note'; }
				if (!$FileAttachment) {
					// /Subj is PDF 1.5 spec.
					if (isset($pl['opt']['subj']) && !$this->PDFA && !$this->PDFX) {
						$annot .= ' /Subj '.$this->_UTF16BEtextstring($pl['opt']['subj']);
					}
					if (!empty($pl['opt']['popup'])) { 
						$annot .= ' /Open true'; 
						$annot .= ' /Popup '.($this->n+1).' 0 R';
					}
					else { $annot .= ' /Open false'; }
				}
				$annot .= ' /P '.$pl['pageobj'].' 0 R';
				$annot .= '>>';
				$this->_out($annot);
				$this->_out('endobj');

				if ($FileAttachment) { 
					$file = @file_get_contents($pl['opt']['file']) or die('mPDF Error: Cannot access file attachment - '.$pl['opt']['file']);
					$filestream = gzcompress($file);
					$this->_newobj();
					$this->_out('<</Type /EmbeddedFile');
					$this->_out('/Length '.strlen($filestream));
					$this->_out('/Filter /FlateDecode');
					$this->_out('>>');
					$this->_putstream($filestream);
					$this->_out('endobj');
				}
				else if (!empty($pl['opt']['popup'])) { 
					$this->_newobj();
					$annot='';
					if (is_array($pl['opt']['popup']) && isset($pl['opt']['popup'][0])) { $x = $pl['opt']['popup'][0] * _MPDFK; }
					else { $x = $pl['x'] * _MPDFK; }
					if (is_array($pl['opt']['popup']) && isset($pl['opt']['popup'][1])) { $y = $hPt - ($pl['opt']['popup'][1] * _MPDFK); }
					else { $y = $hPt - ($pl['y']  * _MPDFK); }
					if (is_array($pl['opt']['popup']) && isset($pl['opt']['popup'][2])) { $w = $pl['opt']['popup'][2] * _MPDFK; }
					else { $w = 180; }
					if (is_array($pl['opt']['popup']) && isset($pl['opt']['popup'][3])) { $h = $pl['opt']['popup'][3] * _MPDFK; }
					else { $h = 120; }
					$rect = sprintf('%.3F %.3F %.3F %.3F', $x, $y-$h, $x+$w, $y);
					$annot .= '<</Type /Annot /Subtype /Popup /Rect ['.$rect.']';
					$annot .= ' /M '.$this->_textstring('D:'.date('YmdHis'));
					if ($this->PDFA || $this->PDFX) { $annot .= ' /F 28'; }
					$annot .= ' /Parent '.($this->n-1).' 0 R';
					$annot .= '>>';
					$this->_out($annot);
					$this->_out('endobj');
				}
			   }
			}
/*-- END ANNOTATIONS --*/

/*-- FORMS --*/
			// Active Forms
			if ( count($this->form->forms) > 0 ) {
				$this->form->_putFormItems($n, $hPt);
			}
/*-- END FORMS --*/
		}
	}
/*-- FORMS --*/
	// Active Forms - Radio Button Group entries
	// Output Radio Button Group form entries (radio_on_obj_id already determined)
	if (count($this->form->form_radio_groups)) {
		$this->form->_putRadioItems($n);
	}
/*-- END FORMS --*/
}


/*-- ANNOTATIONS --*/
function Annotation($text, $x=0, $y=0, $icon='Note', $author='', $subject='', $opacity=0, $colarray=false, $popup='', $file='') {
	if (is_array($colarray) && count($colarray)==3) { $colarray = $this->ConvertColor('rgb('.$colarray[0].','.$colarray[1].','.$colarray[2].')'); }
	if ($colarray === false) { $colarray = $this->ConvertColor('yellow'); }
	if ($x==0) { $x = $this->x; }
	if ($y==0) { $y = $this->y; }
	$page = $this->page;
	if ($page < 1) {	// Document has not been started - assume it's for first page
		$page = 1;
		if ($x==0) { $x = $this->lMargin; }
		if ($y==0) { $y = $this->tMargin; }
	}

	if ($this->PDFA || $this->PDFX) {
		if (($this->PDFA && !$this->PDFAauto) || ($this->PDFX && !$this->PDFXauto)) { $this->PDFAXwarnings[] = "Annotation markers cannot be semi-transparent in PDFA1-b or PDFX/1-a, so they may make underlying text unreadable. (Annotation markers moved to right margin)"; }
		$x = ($this->w) - $this->rMargin*0.66;
	}
	if (!$this->annotMargin) { $y -= $this->FontSize / 2; }

	if (!$opacity && $this->annotMargin) { $opacity = 1; }
	else if (!$opacity) { $opacity = $this->annotOpacity; }

	$an = array('txt' => $text, 'x' => $x, 'y' => $y, 'opt' => array('Icon'=>$icon, 'T'=>$author, 'Subj'=>$subject, 'C'=>$colarray, 'CA'=>$opacity, 'popup'=>$popup, 'file'=>$file));

	if ($this->keep_block_together) {	// Save to array - don't write yet
		$this->ktAnnots[$this->page][]= $an;
		return;
	}
	else if ($this->table_rotate) {
		$this->tbrot_Annots[$this->page][]= $an;
		return;
	}
	else if ($this->kwt) {
		$this->kwt_Annots[$this->page][]= $an;
		return;
	}
	// mPDF 5.0 
	if ($this->writingHTMLheader || $this->writingHTMLfooter) {
		$this->HTMLheaderPageAnnots[]= $an;
		return;
	}
	//Put an Annotation on the page
	$this->PageAnnots[$page][] = $an;
/*-- COLUMNS --*/
	// Save cross-reference to Column buffer
	$ref = count($this->PageAnnots[$this->page])-1;
	$this->columnAnnots[$this->CurrCol][INTVAL($this->x)][INTVAL($this->y)] = $ref;
/*-- END COLUMNS --*/
}
/*-- END ANNOTATIONS --*/


function _putfonts() {
	$nf=$this->n;
	foreach($this->FontFiles as $fontkey=>$info) {
	   // TrueType embedded
	   if (isset($info['type']) && $info['type']=='TTF' && !$info['sip'] && !$info['smp']) {
		$used = true;
		$asSubset = false;
		foreach($this->fonts AS $k=>$f) {
			if ($f['fontkey'] == $fontkey && $f['type']=='TTF') { 
				$used = $f['used']; 
				if ($used) {
					$nChars = (ord($f['cw'][0]) << 8) + ord($f['cw'][1]);
					$usage = intval(count($f['subset'])*100 / $nChars);
					$fsize = $info['length1'];
					// Always subset the very large TTF files
					if ($fsize > ($this->maxTTFFilesize *1024)) { $asSubset = true; }
					else if ($usage < $this->percentSubset) { $asSubset = true; }
				}
				if ($f['unAGlyphs']) $aaSubset = true;	// mPDF 5.4.05
				if ($this->PDFA || $this->PDFX)  $asSubset = false;
				$this->fonts[$k]['asSubset'] = $asSubset;
				break;
			}
		}
		if ($used && !$asSubset) {
			//Font file embedding
			$this->_newobj();
			$this->FontFiles[$fontkey]['n']=$this->n;
			$font='';
			$originalsize = $info['length1'];
			if ($this->repackageTTF || $this->fonts[$fontkey]['TTCfontID']>0) {
				// First see if there is a cached compressed file
				if (file_exists(_MPDF_TTFONTDATAPATH.$fontkey.'.ps.z')) {
					$f=fopen(_MPDF_TTFONTDATAPATH.$fontkey.'.ps.z','rb');
					if(!$f) { $this->Error('Font file .ps.z not found'); }
					while(!feof($f)) { $font .= fread($f, 2048); }
					fclose($f);
					include(_MPDF_TTFONTDATAPATH.$fontkey.'.ps.php');	// sets $originalsize (of repackaged font)
				}
				else {
					if (!class_exists('TTFontFile', false)) { include(_MPDF_PATH .'classes/ttfontsuni.php'); }
					$ttf = new TTFontFile();
					$font = $ttf->repackageTTF($this->FontFiles[$fontkey]['ttffile'], $this->fonts[$fontkey]['TTCfontID'], $this->debugfonts, $this->fonts[$fontkey]['unAGlyphs']);	// mPDF 5.4.05

					$originalsize = strlen($font);
					$font = gzcompress($font);
					unset($ttf);
					if (is_writable(dirname(_MPDF_TTFONTDATAPATH.'x'))) {
						$fh = fopen(_MPDF_TTFONTDATAPATH.$fontkey.'.ps.z',"wb");
						fwrite($fh,$font,strlen($font));
						fclose($fh);
						$fh = fopen(_MPDF_TTFONTDATAPATH.$fontkey.'.ps.php',"wb");
						$len = "<?php \n";
						$len.='$originalsize='.$originalsize.";\n";
						$len.="?>";
						fwrite($fh,$len,strlen($len));
						fclose($fh);
					}
				}
			}
			else {
				// First see if there is a cached compressed file
				if (file_exists(_MPDF_TTFONTDATAPATH.$fontkey.'.z')) {
					$f=fopen(_MPDF_TTFONTDATAPATH.$fontkey.'.z','rb');
					if(!$f) { $this->Error('Font file not found'); }
					while(!feof($f)) { $font .= fread($f, 2048); }
					fclose($f);
				}
				else {
					$f=fopen($this->FontFiles[$fontkey]['ttffile'],'rb');
					if(!$f) { $this->Error('Font file not found'); }
					while(!feof($f)) { $font .= fread($f, 2048); }
					fclose($f);
					$font = gzcompress($font);
					if (is_writable(dirname(_MPDF_TTFONTDATAPATH.'x'))) {
						$fh = fopen(_MPDF_TTFONTDATAPATH.$fontkey.'.z',"wb");
						fwrite($fh,$font,strlen($font));
						fclose($fh);
					}
				}
			}

			$this->_out('<</Length '.strlen($font));
			$this->_out('/Filter /FlateDecode');
			$this->_out('/Length1 '.$originalsize);
			$this->_out('>>');
			$this->_putstream($font);
			$this->_out('endobj');
		}
	   }
	}

	$nfonts = count($this->fonts);
	$fctr = 1;
	foreach($this->fonts as $k=>$font) {
		//Font objects
		$type=$font['type'];
		$name=$font['name'];
		if ((!isset($font['used']) || !$font['used']) && $type=='TTF') { continue; }
		if ($this->progressBar) { $this->UpdateProgressBar(2,intval($fctr*100/$nfonts),'Writing Fonts'); $fctr++; }	// *PROGRESS-BAR*
		if (isset($font['asSubset'])) { $asSubset = $font['asSubset']; }
		else { $asSubset = ''; }
/*-- CJK-FONTS --*/
		if($type=='Type0') { 	// = Adobe CJK Fonts
			$this->fonts[$k]['n']=$this->n+1;
			$this->_newobj();
			$this->_out('<</Type /Font');
			$this->_putType0($font);
		}
		else
/*-- END CJK-FONTS --*/
		if($type=='core') {
			//Standard font
			$this->fonts[$k]['n']=$this->n+1;
			if ($this->PDFA || $this->PDFX) { $this->Error('Core fonts are not allowed in PDF/A1-b or PDFX/1-a files (Times, Helvetica, Courier etc.)'); }
			$this->_newobj();
			$this->_out('<</Type /Font');
			$this->_out('/BaseFont /'.$name);
			$this->_out('/Subtype /Type1');
			if($name!='Symbol' && $name!='ZapfDingbats') {
				$this->_out('/Encoding /WinAnsiEncoding');
			}
			$this->_out('>>');
			$this->_out('endobj');
		} 
		// TrueType embedded SUBSETS for SIP (CJK extB containing Supplementary Ideographic Plane 2)
		// Or Unicode Plane 1 - Supplementary Multilingual Plane
		else if ($type=='TTF' && ($font['sip'] || $font['smp'])) {
		   if (!$font['used']) { continue; }
		   $ssfaid="AA";
		   if (!class_exists('TTFontFile', false)) { include(_MPDF_PATH .'classes/ttfontsuni.php'); }
		   $ttf = new TTFontFile();
		   for($sfid=0;$sfid<count($font['subsetfontids']);$sfid++) {
			$this->fonts[$k]['n'][$sfid]=$this->n+1;		// NB an array for subset
			$subsetname = 'MPDF'.$ssfaid.'+'.$font['name'];
			$ssfaid++;
			$subset = $font['subsets'][$sfid];
			unset($subset[0]);
			$ttfontstream = $ttf->makeSubsetSIP($font['ttffile'], $subset, $font['TTCfontID'], $this->debugfonts);
			$ttfontsize = strlen($ttfontstream);
			$fontstream = gzcompress($ttfontstream);
			$widthstring = '';
			$toUnistring = '';
			foreach($font['subsets'][$sfid] AS $cp=>$u) {
				$w = $this->_getCharWidth($font['cw'], $u); 
				if ($w !== false) {
					$widthstring .= $w.' ';
				}
				else {
					$widthstring .= round($ttf->defaultWidth).' ';
				}
				if ($u > 65535) {
					$utf8 = chr(($u>>18)+240).chr((($u>>12)&63)+128).chr((($u>>6)&63)+128) .chr(($u&63)+128);
					$utf16 = mb_convert_encoding($utf8, 'UTF-16BE', 'UTF-8');
					$l1 = ord($utf16[0]);
					$h1 = ord($utf16[1]);
					$l2 = ord($utf16[2]);
					$h2 = ord($utf16[3]);
					$toUnistring .= sprintf("<%02s> <%02s%02s%02s%02s>\n", strtoupper(dechex($cp)), strtoupper(dechex($l1)), strtoupper(dechex($h1)), strtoupper(dechex($l2)), strtoupper(dechex($h2)));
				}
				else {
					$toUnistring .= sprintf("<%02s> <%04s>\n", strtoupper(dechex($cp)), strtoupper(dechex($u)));
				}
			}

			//Additional Type1 or TrueType font
			$this->_newobj();
			$this->_out('<</Type /Font');
			$this->_out('/BaseFont /'.$subsetname);
			$this->_out('/Subtype /TrueType');
			$this->_out('/FirstChar 0 /LastChar '.(count($font['subsets'][$sfid])-1));
			$this->_out('/Widths '.($this->n+1).' 0 R');
			$this->_out('/FontDescriptor '.($this->n+2).' 0 R');
			$this->_out('/ToUnicode '.($this->n + 3).' 0 R');
			$this->_out('>>');
			$this->_out('endobj');

			//Widths
			$this->_newobj();
			$this->_out('['.$widthstring.']');
			$this->_out('endobj');

			//Descriptor
			$this->_newobj();
			$s='<</Type /FontDescriptor /FontName /'.$subsetname."\n";
			foreach($font['desc'] as $kd=>$v) {
				if ($kd == 'Flags') { $v = $v | 4; $v = $v & ~32; }	// SYMBOLIC font flag
				$s.=' /'.$kd.' '.$v."\n";
			}
			$s.='/FontFile2 '.($this->n + 2).' 0 R';
			$this->_out($s.'>>');
			$this->_out('endobj');

			// ToUnicode
			$this->_newobj();
			$toUni = "/CIDInit /ProcSet findresource begin\n";
			$toUni .= "12 dict begin\n";
			$toUni .= "begincmap\n";
			$toUni .= "/CIDSystemInfo\n";
			$toUni .= "<</Registry (Adobe)\n";
			$toUni .= "/Ordering (UCS)\n";
			$toUni .= "/Supplement 0\n";
			$toUni .= ">> def\n";
			$toUni .= "/CMapName /Adobe-Identity-UCS def\n";
			$toUni .= "/CMapType 2 def\n";
			$toUni .= "1 begincodespacerange\n";
			$toUni .= "<00> <FF>\n";
			$toUni .= "endcodespacerange\n";
			$toUni .= count($font['subsets'][$sfid])." beginbfchar\n";
			$toUni .= $toUnistring;
			$toUni .= "endbfchar\n";
			$toUni .= "endcmap\n";
			$toUni .= "CMapName currentdict /CMap defineresource pop\n";
			$toUni .= "end\n";
			$toUni .= "end\n";

			$this->_out('<</Length '.(strlen($toUni)).'>>');
			$this->_putstream($toUni);
			$this->_out('endobj');

			//Font file 
			$this->_newobj();
			$this->_out('<</Length '.strlen($fontstream));
			$this->_out('/Filter /FlateDecode');
			$this->_out('/Length1 '.$ttfontsize);
			$this->_out('>>');
			$this->_putstream($fontstream);
			$this->_out('endobj');
		   }	// foreach subset
		   unset($ttf);
		} 
		// TrueType embedded SUBSETS or FULL
		else if ($type=='TTF') {
			$this->fonts[$k]['n']=$this->n+1;
			if ($asSubset ) {
				$ssfaid="A";
				if (!class_exists('TTFontFile', false)) { include(_MPDF_PATH .'classes/ttfontsuni.php'); }
				$ttf = new TTFontFile();
				$fontname = 'MPDFA'.$ssfaid.'+'.$font['name'];
				$subset = $font['subset'];
				unset($subset[0]);
				$ttfontstream = $ttf->makeSubset($font['ttffile'], $subset, $font['TTCfontID'], $this->debugfonts, $font['unAGlyphs']);	// mPDF 5.4.05
				$ttfontsize = strlen($ttfontstream);
				$fontstream = gzcompress($ttfontstream);
				$codeToGlyph = $ttf->codeToGlyph;
				unset($codeToGlyph[0]);
			}
			else { $fontname = $font['name']; }
			// Type0 Font
			// A composite font - a font composed of other fonts, organized hierarchically
			$this->_newobj();
			$this->_out('<</Type /Font');
			$this->_out('/Subtype /Type0');
			$this->_out('/BaseFont /'.$fontname.'');
			$this->_out('/Encoding /Identity-H'); 
			$this->_out('/DescendantFonts ['.($this->n + 1).' 0 R]');
			$this->_out('/ToUnicode '.($this->n + 2).' 0 R');
			$this->_out('>>');
			$this->_out('endobj');

			// CIDFontType2
			// A CIDFont whose glyph descriptions are based on TrueType font technology
			$this->_newobj();
			$this->_out('<</Type /Font');
			$this->_out('/Subtype /CIDFontType2');
			$this->_out('/BaseFont /'.$fontname.'');
			$this->_out('/CIDSystemInfo '.($this->n + 2).' 0 R'); 
			$this->_out('/FontDescriptor '.($this->n + 3).' 0 R');
			if (isset($font['desc']['MissingWidth'])){
				$this->_out('/DW '.$font['desc']['MissingWidth'].''); 
			}

			if (!$asSubset && file_exists(_MPDF_TTFONTDATAPATH.$font['fontkey'].'.cw')) {
					$w = '';
					$w=file_get_contents(_MPDF_TTFONTDATAPATH.$font['fontkey'].'.cw');
					$this->_out($w);
			}
			else {
				$this->_putTTfontwidths($font, $asSubset, $ttf->maxUni);
			}

			$this->_out('/CIDToGIDMap '.($this->n + 4).' 0 R');
			$this->_out('>>');
			$this->_out('endobj');

			// ToUnicode
			$this->_newobj();
			$toUni = "/CIDInit /ProcSet findresource begin\n";
			$toUni .= "12 dict begin\n";
			$toUni .= "begincmap\n";
			$toUni .= "/CIDSystemInfo\n";
			$toUni .= "<</Registry (Adobe)\n";
			$toUni .= "/Ordering (UCS)\n";
			$toUni .= "/Supplement 0\n";
			$toUni .= ">> def\n";
			$toUni .= "/CMapName /Adobe-Identity-UCS def\n";
			$toUni .= "/CMapType 2 def\n";
			$toUni .= "1 begincodespacerange\n";
			$toUni .= "<0000> <FFFF>\n";
			$toUni .= "endcodespacerange\n";
			$toUni .= "1 beginbfrange\n";
			$toUni .= "<0000> <FFFF> <0000>\n";
			$toUni .= "endbfrange\n";
			$toUni .= "endcmap\n";
			$toUni .= "CMapName currentdict /CMap defineresource pop\n";
			$toUni .= "end\n";
			$toUni .= "end\n";
			$this->_out('<</Length '.(strlen($toUni)).'>>');
			$this->_putstream($toUni);
			$this->_out('endobj');


			// CIDSystemInfo dictionary
			$this->_newobj();
			$this->_out('<</Registry (Adobe)'); 
			$this->_out('/Ordering (UCS)');
			$this->_out('/Supplement 0');
			$this->_out('>>');
			$this->_out('endobj');

			// Font descriptor
			$this->_newobj();
			$this->_out('<</Type /FontDescriptor');
			$this->_out('/FontName /'.$fontname);
			foreach($font['desc'] as $kd=>$v) {
				if ($asSubset && $kd == 'Flags') { $v = $v | 4; $v = $v & ~32; }	// SYMBOLIC font flag
				$this->_out(' /'.$kd.' '.$v);
			}
			if ($font['panose']) {
				$this->_out(' /Style << /Panose <'.$font['panose'].'> >>');
			}
			if ($asSubset ) {
				$this->_out('/FontFile2 '.($this->n + 2).' 0 R');
			}
			else if ($font['fontkey']) {
				// obj ID of a stream containing a TrueType font program
				$this->_out('/FontFile2 '.$this->FontFiles[$font['fontkey']]['n'].' 0 R');
			}
			$this->_out('>>');
			$this->_out('endobj');

			// Embed CIDToGIDMap
			// A specification of the mapping from CIDs to glyph indices
			if ($asSubset ) {
				$cidtogidmap = '';
				$cidtogidmap = str_pad('', 256*256*2, "\x00");
				foreach($codeToGlyph as $cc=>$glyph) {
					$cidtogidmap[$cc*2] = chr($glyph >> 8);
					$cidtogidmap[$cc*2 + 1] = chr($glyph & 0xFF);
				}
				$cidtogidmap = gzcompress($cidtogidmap);
			}
			else {
				// First see if there is a cached CIDToGIDMapfile
				$cidtogidmap = '';
				if (file_exists(_MPDF_TTFONTDATAPATH.$font['fontkey'].'.cgm')) {
					$f=fopen(_MPDF_TTFONTDATAPATH.$font['fontkey'].'.cgm','rb');
					while(!feof($f)) { $cidtogidmap .= fread($f, 2048); }
					fclose($f);
				}
				else {
					if (!class_exists('TTFontFile', false)) { include(_MPDF_PATH .'classes/ttfontsuni.php'); }
					$ttf = new TTFontFile();
					$charToGlyph = $ttf->getCTG($font['ttffile'], $font['TTCfontID'], $this->debugfonts, $font['unAGlyphs']);	// mPDF 5.4.05
					$cidtogidmap = str_pad('', 256*256*2, "\x00");
					foreach($charToGlyph as $cc=>$glyph) {
						$cidtogidmap[$cc*2] = chr($glyph >> 8);
						$cidtogidmap[$cc*2 + 1] = chr($glyph & 0xFF);
					}
					unset($ttf);
					$cidtogidmap = gzcompress($cidtogidmap);
					if (is_writable(dirname(_MPDF_TTFONTDATAPATH.'x'))) {
						$fh = fopen(_MPDF_TTFONTDATAPATH.$font['fontkey'].'.cgm',"wb");
						fwrite($fh,$cidtogidmap,strlen($cidtogidmap));
						fclose($fh);
					}
				}
			}
			$this->_newobj();
			$this->_out('<</Length '.strlen($cidtogidmap).'');
			$this->_out('/Filter /FlateDecode');
			$this->_out('>>');
			$this->_putstream($cidtogidmap);
			$this->_out('endobj');

			//Font file 
			if ($asSubset ) {
				$this->_newobj();
				$this->_out('<</Length '.strlen($fontstream));
				$this->_out('/Filter /FlateDecode');
				$this->_out('/Length1 '.$ttfontsize);
				$this->_out('>>');
				$this->_putstream($fontstream);
				$this->_out('endobj');
				unset($ttf);
			}
		} 
		else { $this->Error('Unsupported font type: '.$type.' ('.$name.')'); }
	}
}



function _putTTfontwidths(&$font, $asSubset, $maxUni) {
	if ($asSubset && file_exists(_MPDF_TTFONTDATAPATH.$font['fontkey'].'.cw127.php')) {
		include(_MPDF_TTFONTDATAPATH.$font['fontkey'].'.cw127.php') ;
		$startcid = 128;
	}
	else {
		$rangeid = 0;
		$range = array();
		$prevcid = -2;
		$prevwidth = -1;
		$interval = false;
		$startcid = 1;
	}
	if ($asSubset) { $cwlen = $maxUni + 1; }
	else { $cwlen = (strlen($font['cw'])/2); }

	// for each character
	for ($cid=$startcid; $cid<$cwlen; $cid++) {
		if ($cid==128 && $asSubset && (!file_exists(_MPDF_TTFONTDATAPATH.$font['fontkey'].'.cw127.php'))) {
			if (is_writable(dirname(_MPDF_TTFONTDATAPATH.'x'))) {
				$fh = fopen(_MPDF_TTFONTDATAPATH.$font['fontkey'].'.cw127.php',"wb");
				$cw127='<?php'."\n";
				$cw127.='$rangeid='.$rangeid.";\n";
				$cw127.='$prevcid='.$prevcid.";\n";
				$cw127.='$prevwidth='.$prevwidth.";\n";
				if ($interval) { $cw127.='$interval=true'.";\n"; }
				else { $cw127.='$interval=false'.";\n"; }
				$cw127.='$range='.var_export($range,true).";\n";
				$cw127.="?>";
				fwrite($fh,$cw127,strlen($cw127));
				fclose($fh);
			}
		}
		if ($font['cw'][$cid*2] == "\00" && $font['cw'][$cid*2+1] == "\00") { continue; }
		$width = (ord($font['cw'][$cid*2]) << 8) + ord($font['cw'][$cid*2+1]);
		if ($width == 65535) { $width = 0; }
		if ($asSubset && $cid > 255 && (!isset($font['subset'][$cid]) || !$font['subset'][$cid])) {
			continue;
		}
		if (!isset($font['dw']) || (isset($font['dw']) && $width != $font['dw'])) {
			if ($cid == ($prevcid + 1)) {
				// consecutive CID
				if ($width == $prevwidth) {
					if ($width == $range[$rangeid][0]) {
						$range[$rangeid][] = $width;
					} else {
						array_pop($range[$rangeid]);
						// new range
						$rangeid = $prevcid;
						$range[$rangeid] = array();
						$range[$rangeid][] = $prevwidth;
						$range[$rangeid][] = $width;
					}
					$interval = true;
					$range[$rangeid]['interval'] = true;
				} else {
					if ($interval) {
						// new range
						$rangeid = $cid;
						$range[$rangeid] = array();
						$range[$rangeid][] = $width;
					} else {
						$range[$rangeid][] = $width;
					}
					$interval = false;
				}
			} else {
				// new range
				$rangeid = $cid;
				$range[$rangeid] = array();
				$range[$rangeid][] = $width;
				$interval = false;
			}
			$prevcid = $cid;
			$prevwidth = $width;
		}
	}
	$w = $this->_putfontranges($range);
	$this->_out($w);
	if (!$asSubset) {
		if (is_writable(dirname(_MPDF_TTFONTDATAPATH.'x'))) {
			$fh = fopen(_MPDF_TTFONTDATAPATH.$font['fontkey'].'.cw',"wb");
			fwrite($fh,$w,strlen($w));
			fclose($fh);
		}
	}
}

function _putfontranges(&$range) {
	// optimize ranges
	$prevk = -1;
	$nextk = -1;
	$prevint = false;
	foreach ($range as $k => $ws) {
		$cws = count($ws);
		if (($k == $nextk) AND (!$prevint) AND ((!isset($ws['interval'])) OR ($cws < 4))) {
			if (isset($range[$k]['interval'])) {
				unset($range[$k]['interval']);
			}
			$range[$prevk] = array_merge($range[$prevk], $range[$k]);
			unset($range[$k]);
		} else {
			$prevk = $k;
		}
		$nextk = $k + $cws;
		if (isset($ws['interval'])) {
			if ($cws > 3) {
				$prevint = true;
			} else {
				$prevint = false;
			}
			unset($range[$k]['interval']);
			--$nextk;
		} else {
			$prevint = false;
		}
	}
	// output data
	$w = '';
	foreach ($range as $k => $ws) {
		if (count(array_count_values($ws)) == 1) {
			// interval mode is more compact
			$w .= ' '.$k.' '.($k + count($ws) - 1).' '.$ws[0];
		} else {
			// range mode
			$w .= ' '.$k.' [ '.implode(' ', $ws).' ]' . "\n";
		}
	}
	return '/W ['.$w.' ]';
}


function _putfontwidths(&$font, $cidoffset=0) {
	ksort($font['cw']);
	unset($font['cw'][65535]);
	$rangeid = 0;
	$range = array();
	$prevcid = -2;
	$prevwidth = -1;
	$interval = false;
	// for each character
	foreach ($font['cw'] as $cid => $width) {
		$cid -= $cidoffset;
		if (!isset($font['dw']) || (isset($font['dw']) && $width != $font['dw'])) {
			if ($cid == ($prevcid + 1)) {
				// consecutive CID
				if ($width == $prevwidth) {
					if ($width == $range[$rangeid][0]) {
						$range[$rangeid][] = $width;
					} else {
						array_pop($range[$rangeid]);
						// new range
						$rangeid = $prevcid;
						$range[$rangeid] = array();
						$range[$rangeid][] = $prevwidth;
						$range[$rangeid][] = $width;
					}
					$interval = true;
					$range[$rangeid]['interval'] = true;
				} else {
					if ($interval) {
						// new range
						$rangeid = $cid;
						$range[$rangeid] = array();
						$range[$rangeid][] = $width;
					} else {
						$range[$rangeid][] = $width;
					}
					$interval = false;
				}
			} else {
				// new range
				$rangeid = $cid;
				$range[$rangeid] = array();
				$range[$rangeid][] = $width;
				$interval = false;
			}
			$prevcid = $cid;
			$prevwidth = $width;
		}
	}
	$this->_out($this->_putfontranges($range));
}


/*-- CJK-FONTS --*/

// from class PDF_Chinese CJK EXTENSIONS
function _putType0(&$font)
{
	//Type0
	$this->_out('/Subtype /Type0');
	$this->_out('/BaseFont /'.$font['name'].'-'.$font['CMap']);
	$this->_out('/Encoding /'.$font['CMap']);
	$this->_out('/DescendantFonts ['.($this->n+1).' 0 R]');
	$this->_out('>>');
	$this->_out('endobj');
	//CIDFont
	$this->_newobj();
	$this->_out('<</Type /Font');
	$this->_out('/Subtype /CIDFontType0');
	$this->_out('/BaseFont /'.$font['name']);

	$cidinfo = '/Registry '.$this->_textstring('Adobe');
	$cidinfo .= ' /Ordering '.$this->_textstring($font['registry']['ordering']);
	$cidinfo .= ' /Supplement '.$font['registry']['supplement'];
	$this->_out('/CIDSystemInfo <<'.$cidinfo.'>>');

	$this->_out('/FontDescriptor '.($this->n+1).' 0 R');
	if (isset($font['MissingWidth'])){
		$this->_out('/DW '.$font['MissingWidth'].''); 
	}
	$this->_putfontwidths($font, 31);
	$this->_out('>>');
	$this->_out('endobj');

	//Font descriptor
	$this->_newobj();
	$s = '<</Type /FontDescriptor /FontName /'.$font['name'];
	foreach ($font['desc'] as $k => $v) {
		if ($k != 'Style') {
			$s .= ' /'.$k.' '.$v.'';
		}
	}
	$this->_out($s.'>>');
	$this->_out('endobj');
}
/*-- END CJK-FONTS --*/



function _putimages()
{
	$filter=($this->compress) ? '/Filter /FlateDecode ' : '';
	reset($this->images);
	while(list($file,$info)=each($this->images)) {
		$this->_newobj();
		$this->images[$file]['n']=$this->n;
		$this->_out('<</Type /XObject');
		$this->_out('/Subtype /Image');
		$this->_out('/Width '.$info['w']);
		$this->_out('/Height '.$info['h']);
		if (isset($info['masked'])) {
			$this->_out('/SMask '.($this->n - 1).' 0 R');
		}
		if($info['cs']=='Indexed') {
			if ($this->PDFX || ($this->PDFA && $this->restrictColorSpace==3)) { $this->Error("PDFA1-b and PDFX/1-a files do not permit using mixed colour space (".$file.")."); }
			$this->_out('/ColorSpace [/Indexed /DeviceRGB '.(strlen($info['pal'])/3-1).' '.($this->n+1).' 0 R]');
		}
		else {
			$this->_out('/ColorSpace /'.$info['cs']);
			if($info['cs']=='DeviceCMYK') {
				if ($this->PDFA && $this->restrictColorSpace!=3) { $this->Error("PDFA1-b does not permit Images using mixed colour space (".$file.")."); }
				if($info['type']=='jpg') { $this->_out('/Decode [1 0 1 0 1 0 1 0]'); }
			}
			else if ($info['cs']=='DeviceRGB' && ($this->PDFX || ($this->PDFA && $this->restrictColorSpace==3))) { $this->Error("PDFA1-b and PDFX/1-a files do not permit using mixed colour space (".$file.")."); }
		}
		$this->_out('/BitsPerComponent '.$info['bpc']);
		if (isset($info['f']) && $info['f']) { $this->_out('/Filter /'.$info['f']); }
		if(isset($info['parms'])) { $this->_out($info['parms']); }
		if(isset($info['trns']) and is_array($info['trns'])) {
			$trns='';
			for($i=0;$i<count($info['trns']);$i++)
				$trns.=$info['trns'][$i].' '.$info['trns'][$i].' ';
			$this->_out('/Mask ['.$trns.']');
		}
		$this->_out('/Length '.strlen($info['data']).'>>');
		$this->_putstream($info['data']);

		unset($this->images[$file]['data']);
		$this->_out('endobj');
		//Palette
		if($info['cs']=='Indexed') {
			$this->_newobj();
			$pal=($this->compress) ? gzcompress($info['pal']) : $info['pal'];
			$this->_out('<<'.$filter.'/Length '.strlen($pal).'>>');
			$this->_putstream($pal);
			$this->_out('endobj');
		}
	}
}

function _putinfo()
{
	$this->_out('/Producer '.$this->_UTF16BEtextstring('mPDF '.mPDF_VERSION));
	if(!empty($this->title))
		$this->_out('/Title '.$this->_UTF16BEtextstring($this->title));
	if(!empty($this->subject))
		$this->_out('/Subject '.$this->_UTF16BEtextstring($this->subject));
	if(!empty($this->author))
		$this->_out('/Author '.$this->_UTF16BEtextstring($this->author));
	if(!empty($this->keywords))
		$this->_out('/Keywords '.$this->_UTF16BEtextstring($this->keywords));
	if(!empty($this->creator))
		$this->_out('/Creator '.$this->_UTF16BEtextstring($this->creator));

	$z = date('O'); // +0200
	$offset = substr($z,0,3)."'".substr($z,3,2)."'";
	$this->_out('/CreationDate '.$this->_textstring(date('YmdHis').$offset));
	$this->_out('/ModDate '.$this->_textstring(date('YmdHis').$offset));
	if ($this->PDFX) {
		$this->_out('/Trapped/False');
		$this->_out('/GTS_PDFXVersion(PDF/X-1a:2003)');
	}
}

function _putmetadata() {
	$this->_newobj();
	$this->MetadataRoot = $this->n;
	$Producer = 'mPDF '.mPDF_VERSION;
	$z = date('O'); // +0200
	$offset = substr($z,0,3).':'.substr($z,3,2);
	$CreationDate = date('Y-m-d\TH:i:s').$offset;	// 2006-03-10T10:47:26-05:00 2006-06-19T09:05:17Z
	$uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff),
			mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)  );


	$m = '<?xpacket begin="'.chr(239).chr(187).chr(191).'" id="W5M0MpCehiHzreSzNTczkc9d"?>'."\n";	// begin = FEFF BOM
	$m .= ' <x:xmpmeta xmlns:x="adobe:ns:meta/" x:xmptk="3.1-701">'."\n";
	$m .= '  <rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">'."\n";
	$m .= '   <rdf:Description rdf:about="uuid:'.$uuid.'" xmlns:pdf="http://ns.adobe.com/pdf/1.3/">'."\n";
	$m .= '    <pdf:Producer>'.$Producer.'</pdf:Producer>'."\n";
	if(!empty($this->keywords)) { $m .= '    <pdf:Keywords>'.$this->keywords.'</pdf:Keywords>'."\n"; }
	$m .= '   </rdf:Description>'."\n";

	$m .= '   <rdf:Description rdf:about="uuid:'.$uuid.'" xmlns:xmp="http://ns.adobe.com/xap/1.0/">'."\n";
	$m .= '    <xmp:CreateDate>'.$CreationDate.'</xmp:CreateDate>'."\n";
	$m .= '    <xmp:ModifyDate>'.$CreationDate.'</xmp:ModifyDate>'."\n";
	$m .= '    <xmp:MetadataDate>'.$CreationDate.'</xmp:MetadataDate>'."\n";
	if(!empty($this->creator)) { $m .= '    <xmp:CreatorTool>'.$this->creator.'</xmp:CreatorTool>'."\n"; }
	$m .= '   </rdf:Description>'."\n";

	// DC elements
	$m .= '   <rdf:Description rdf:about="uuid:'.$uuid.'" xmlns:dc="http://purl.org/dc/elements/1.1/">'."\n";
	$m .= '    <dc:format>application/pdf</dc:format>'."\n";
	if(!empty($this->title)) {
		$m .= '    <dc:title>
     <rdf:Alt>
      <rdf:li xml:lang="x-default">'.$this->title.'</rdf:li>
     </rdf:Alt>
    </dc:title>'."\n";
	}
	if(!empty($this->keywords)) {
		$m .= '    <dc:subject>
     <rdf:Bag>
      <rdf:li>'.$this->keywords.'</rdf:li>
     </rdf:Bag>
    </dc:subject>'."\n";
	}
	if(!empty($this->subject)) {
		$m .= '    <dc:description>
     <rdf:Alt>
      <rdf:li xml:lang="x-default">'.$this->subject.'</rdf:li>
     </rdf:Alt>
    </dc:description>'."\n";
	}
	if(!empty($this->author)) {
		$m .= '    <dc:creator>
     <rdf:Seq>
      <rdf:li>'.$this->author.'</rdf:li>
     </rdf:Seq>
    </dc:creator>'."\n";
	}
	$m .= '   </rdf:Description>'."\n";


	// This bit is specific to PDFX-1a
	if ($this->PDFX) {
		$m .= '   <rdf:Description rdf:about="uuid:'.$uuid.'" xmlns:pdfx="http://ns.adobe.com/pdfx/1.3/" pdfx:Apag_PDFX_Checkup="1.3" pdfx:GTS_PDFXConformance="PDF/X-1a:2003" pdfx:GTS_PDFXVersion="PDF/X-1:2003"/>'."\n";
	}

	// This bit is specific to PDFA-1b
	else if ($this->PDFA) {
		$m .= '   <rdf:Description rdf:about="uuid:'.$uuid.'" xmlns:pdfaid="http://www.aiim.org/pdfa/ns/id/" >'."\n";
		$m .= '    <pdfaid:part>1</pdfaid:part>'."\n";
		$m .= '    <pdfaid:conformance>B</pdfaid:conformance>'."\n";
		$m .= '    <pdfaid:amd>2005</pdfaid:amd>'."\n";
		$m .= '   </rdf:Description>'."\n";
	}

	$m .= '   <rdf:Description rdf:about="uuid:'.$uuid.'" xmlns:xmpMM="http://ns.adobe.com/xap/1.0/mm/">'."\n";
	$m .= '    <xmpMM:DocumentID>uuid:'.$uuid.'</xmpMM:DocumentID>'."\n";
	$m .= '   </rdf:Description>'."\n";
	$m .= '  </rdf:RDF>'."\n";
	$m .= ' </x:xmpmeta>'."\n";
	$m .= str_repeat(str_repeat(' ',100)."\n",20);	// 2-4kB whitespace padding required
	$m .= '<?xpacket end="w"?>';	// "r" read only
	$this->_out('<</Type/Metadata/Subtype/XML/Length '.strlen($m).'>>');
	$this->_putstream($m);
	$this->_out('endobj');
}

function _putoutputintent() {
	$this->_newobj();
	$this->OutputIntentRoot = $this->n;
	$this->_out('<</Type /OutputIntent');

	if ($this->PDFA) {
		$this->_out('/S /GTS_PDFA1');
		if ($this->ICCProfile) {
			$this->_out('/Info ('.preg_replace('/_/',' ',$this->ICCProfile).')');
			$this->_out('/OutputConditionIdentifier (Custom)');
			$this->_out('/OutputCondition ()');
		}
		else {
			$this->_out('/Info (sRGB IEC61966-2.1)');
			$this->_out('/OutputConditionIdentifier (sRGB IEC61966-2.1)');
			$this->_out('/OutputCondition ()');
		}
		$this->_out('/DestOutputProfile '.($this->n+1).' 0 R');
	}
	else if ($this->PDFX) {	// always a CMYK profile
		$this->_out('/S /GTS_PDFX');
		if ($this->ICCProfile) {
			$this->_out('/Info ('.preg_replace('/_/',' ',$this->ICCProfile).')');
			$this->_out('/OutputConditionIdentifier (Custom)');
			$this->_out('/OutputCondition ()');
			$this->_out('/DestOutputProfile '.($this->n+1).' 0 R');
		}
		else {
			$this->_out('/Info (CGATS TR 001)');
			$this->_out('/OutputConditionIdentifier (CGATS TR 001)');
			$this->_out('/OutputCondition (CGATS TR 001 (SWOP))');
			$this->_out('/RegistryName (http://www.color.org)');
		}
	}
	$this->_out('>>');
	$this->_out('endobj');

	if ($this->PDFX && !$this->ICCProfile) { return; } // no ICCProfile embedded

	$this->_newobj();
	if ($this->ICCProfile)
		$s = file_get_contents(_MPDF_PATH.'iccprofiles/'.$this->ICCProfile.'.icc');
	else 
		$s = file_get_contents(_MPDF_PATH.'iccprofiles/sRGB_IEC61966-2-1.icc');
	if ($this->compress) { $s = gzcompress($s); }
	$this->_out('<<');
	if ($this->PDFX || ($this->PDFA && $this->restrictColorSpace == 3)) { $this->_out('/N 4'); }
	else { $this->_out('/N 3'); }
	if ($this->compress)
		$this->_out('/Filter /FlateDecode ');
	$this->_out('/Length '.strlen($s).'>>');
	$this->_putstream($s);
	$this->_out('endobj');
}


function _putcatalog() {
	$this->_out('/Type /Catalog');
	$this->_out('/Pages 1 0 R');
	if($this->ZoomMode=='fullpage')	$this->_out('/OpenAction [3 0 R /Fit]');
	elseif($this->ZoomMode=='fullwidth') $this->_out('/OpenAction [3 0 R /FitH null]');
	elseif($this->ZoomMode=='real')	$this->_out('/OpenAction [3 0 R /XYZ null null 1]');
	elseif(!is_string($this->ZoomMode))	$this->_out('/OpenAction [3 0 R /XYZ null null '.($this->ZoomMode/100).']');
	else	$this->_out('/OpenAction [3 0 R /XYZ null null null]');
	if($this->LayoutMode=='single')	$this->_out('/PageLayout /SinglePage');
	elseif($this->LayoutMode=='continuous')	$this->_out('/PageLayout /OneColumn');
	elseif($this->LayoutMode=='twoleft')	$this->_out('/PageLayout /TwoColumnLeft');
	elseif($this->LayoutMode=='tworight')	$this->_out('/PageLayout /TwoColumnRight');
	elseif($this->LayoutMode=='two') {
	  if ($this->mirrorMargins) { $this->_out('/PageLayout /TwoColumnRight'); }
	  else { $this->_out('/PageLayout /TwoColumnLeft'); }
	}

/*-- BOOKMARKS --*/
	if(count($this->BMoutlines)>0) {
	      $this->_out('/Outlines '.$this->OutlineRoot.' 0 R');
	      $this->_out('/PageMode /UseOutlines');
	}
/*-- END BOOKMARKS --*/
	if(is_int(strpos($this->DisplayPreferences,'FullScreen'))) $this->_out('/PageMode /FullScreen');

	// Metadata
	if ($this->PDFA || $this->PDFX) { 
		$this->_out('/Metadata '.$this->MetadataRoot.' 0 R');
	}
	// OutputIntents
	if ($this->PDFA || $this->PDFX || $this->ICCProfile) { 
		$this->_out('/OutputIntents ['.$this->OutputIntentRoot.' 0 R]');
	}

/*-- FORMS --*/
	if (count($this->form->forms)>0) {
		$this->form->_putFormsCatalog();
	}
/*-- END FORMS --*/
	if ( isset($this->js) ) {
		$this->_out('/Names << /JavaScript '.($this->n_js).' 0 R >> ');
	}

	if($this->DisplayPreferences || $this->directionality == 'rtl' || $this->mirrorMargins) {
		$this->_out('/ViewerPreferences<<');
		if(is_int(strpos($this->DisplayPreferences,'HideMenubar'))) $this->_out('/HideMenubar true');
		if(is_int(strpos($this->DisplayPreferences,'HideToolbar'))) $this->_out('/HideToolbar true');
		if(is_int(strpos($this->DisplayPreferences,'HideWindowUI'))) $this->_out('/HideWindowUI true');
		if(is_int(strpos($this->DisplayPreferences,'DisplayDocTitle'))) $this->_out('/DisplayDocTitle true');
		if(is_int(strpos($this->DisplayPreferences,'CenterWindow'))) $this->_out('/CenterWindow true');
		if(is_int(strpos($this->DisplayPreferences,'FitWindow'))) $this->_out('/FitWindow true');
		// /PrintScaling is PDF 1.6 spec.
		if(is_int(strpos($this->DisplayPreferences,'NoPrintScaling')) && !$this->PDFA && !$this->PDFX) 
			$this->_out('/PrintScaling /None');
		if($this->directionality == 'rtl') $this->_out('/Direction /R2L');
		// /Duplex is PDF 1.7 spec.
		if($this->mirrorMargins && !$this->PDFA && !$this->PDFX) {
			// if ($this->DefOrientation=='P') $this->_out('/Duplex /DuplexFlipShortEdge');
			$this->_out('/Duplex /DuplexFlipLongEdge');	// PDF v1.7+
		}
		$this->_out('>>');
	}
	// mPDF 5.6.01
	if($this->open_layer_pane && ($this->hasOC || count($this->layers)))
		$this->_out('/PageMode /UseOC');

	// mPDF 5.6.01
	if ($this->hasOC || count($this->layers)) {
		$p = $v = $h = $l = $loff = $lall = $as = '';	// mPDF 5.6.28
		if ($this->hasOC) {
			if (($this->hasOC & 1) == 1) $p=$this->n_ocg_print.' 0 R';
			if (($this->hasOC & 2) == 2) $v=$this->n_ocg_view.' 0 R';
			if (($this->hasOC & 4) == 4) $h=$this->n_ocg_hidden.' 0 R';
			$as="<</Event /Print /OCGs [$p $v $h] /Category [/Print]>> <</Event /View /OCGs [$p $v $h] /Category [/View]>>";
		}

		if(count($this->layers)) {
			foreach($this->layers as $k=>$layer) {	// mPDF 5.6.28
				if (strtolower($this->layerDetails[$k]['state'])=='hidden') { $loff .= $layer['n'].' 0 R '; }
				else { $l .= $layer['n'].' 0 R '; }
				$lall .= $layer['n'].' 0 R ';
			}
		}
		$this->_out("/OCProperties <</OCGs [$p $v $h $lall] /D <</ON [$p $l] /OFF [$v $h $loff] ");	// mPDF 5.6.28
		$this->_out("/Order [$v $p $h $lall] ");	// mPDF 5.6.28
		if ($as) $this->_out("/AS [$as] ");
		$this->_out(">>>>");

	}

}

// Inactive function left for backwards compatability
function SetUserRights($enable=true, $annots="", $form="", $signature="") {
	// Does nothing
}

function _enddoc() {
	if ($this->progressBar) { $this->UpdateProgressBar(2,'10','Writing Headers & Footers'); }	// *PROGRESS-BAR*
	$this->_puthtmlheaders();	// *HTMLHEADERS-FOOTERS*
	if ($this->progressBar) { $this->UpdateProgressBar(2,'20','Writing Pages'); }	// *PROGRESS-BAR*
	// Remove references to unused fonts (usually default font)
	foreach($this->fonts as $fk=>$font) {
	   if (!$font['used'] && ($font['type']=='TTF')) { 
		if ($font['sip'] || $font['smp']) {
			foreach($font['subsetfontids'] AS $k => $fid) {
				foreach($this->pages AS $pn=>$page) { 
					$this->pages[$pn] = preg_replace('/\s\/F'.$fid.' \d[\d.]* Tf\s/is',' ',$this->pages[$pn]); 
				}
			}
		}
		else { 
				foreach($this->pages AS $pn=>$page) { 
					$this->pages[$pn] = preg_replace('/\s\/F'.$font['i'].' \d[\d.]* Tf\s/is',' ',$this->pages[$pn]); 
				}
		}
	   }
	}

	// mPDF 5.6.01 - LAYERS
	if (count($this->layers)) {
	  foreach($this->pages AS $pn=>$page) { 
		preg_match_all('/\/OCZ-index \/ZI(\d+) BDC(.*?)(EMCZ)-index/is',$this->pages[$pn],$m1);
		preg_match_all('/\/OCBZ-index \/ZI(\d+) BDC(.*?)(EMCBZ)-index/is',$this->pages[$pn],$m2);
		preg_match_all('/\/OCGZ-index \/ZI(\d+) BDC(.*?)(EMCGZ)-index/is',$this->pages[$pn],$m3);
		$m = array();
		for ($i=0;$i<4;$i++) {
			$m[$i] = array_merge($m1[$i],$m2[$i],$m3[$i]);
		}
		if (count($m[0])) {
			$sortarr = array();
			for($i=0;$i<count($m[0]);$i++) {
				$key = $m[1][$i]*2;
				if ($m[3][$i]=='EMCZ') $key +=2;	// background first then gradient then normal
				else if ($m[3][$i]=='EMCGZ') $key +=1;
				$sortarr[$i] = $key;
			} 
			asort($sortarr);
			foreach($sortarr AS $i=>$k) {
				$this->pages[$pn] = str_replace($m[0][$i],'',$this->pages[$pn] );
				$this->pages[$pn] .= "\n".$m[0][$i]."\n";
			} 
			$this->pages[$pn] = preg_replace('/\/OC[BG]{0,1}Z-index \/ZI(\d+) BDC/is','/OC /ZI\\1 BDC ',$this->pages[$pn]); 
			$this->pages[$pn] = preg_replace('/EMC[BG]{0,1}Z-index/is','EMC',$this->pages[$pn]); 
		}
	  }
	}

	$this->_putpages();
	if ($this->progressBar) { $this->UpdateProgressBar(2,'30','Writing document resources'); }	// *PROGRESS-BAR*

	$this->_putresources();
	//Info
	$this->_newobj();
	$this->InfoRoot = $this->n;
	$this->_out('<<');
	if ($this->progressBar) { $this->UpdateProgressBar(2,'80','Writing document info'); }	// *PROGRESS-BAR*
	$this->_putinfo();
	$this->_out('>>');
	$this->_out('endobj');

	// METADATA
	if ($this->PDFA || $this->PDFX) { $this->_putmetadata(); }
	// OUTPUTINTENT
	if ($this->PDFA || $this->PDFX || $this->ICCProfile) { $this->_putoutputintent(); }

	//Catalog
	$this->_newobj();
	$this->_out('<<');
	if ($this->progressBar) { $this->UpdateProgressBar(2,'90','Writing document catalog'); }	// *PROGRESS-BAR*
	$this->_putcatalog();
	$this->_out('>>');
	$this->_out('endobj');
	//Cross-ref
	$o=strlen($this->buffer);
	$this->_out('xref');
	$this->_out('0 '.($this->n+1));
	$this->_out('0000000000 65535 f ');
	for($i=1; $i <= $this->n ; $i++)
		$this->_out(sprintf('%010d 00000 n ',$this->offsets[$i]));
	//Trailer
	$this->_out('trailer');
	$this->_out('<<');
	$this->_puttrailer();
	$this->_out('>>');
	$this->_out('startxref');
	$this->_out($o);

	$this->buffer .= '%%EOF';
	$this->state=3;
/*-- IMPORTS --*/

	if ($this->enableImports && count($this->parsers) > 0) {
	  	foreach ($this->parsers as $k => $_){
			$this->parsers[$k]->closeFile();
			$this->parsers[$k] = null;
			unset($this->parsers[$k]);
		}
	}
/*-- END IMPORTS --*/
}

function _beginpage($orientation,$mgl='',$mgr='',$mgt='',$mgb='',$mgh='',$mgf='',$ohname='',$ehname='',$ofname='',$efname='',$ohvalue=0,$ehvalue=0,$ofvalue=0,$efvalue=0,$pagesel='',$newformat='') {
	if (!($pagesel && $this->page==1 && (sprintf("%0.4f", $this->y)==sprintf("%0.4f", $this->tMargin)))) { 
		$this->page++;
		$this->pages[$this->page]='';
	}
	$this->state=2;
	$resetHTMLHeadersrequired = false;

	if ($newformat) { $this->_setPageSize($newformat, $orientation); }
/*-- CSS-PAGE --*/
	// Paged media (page-box)

	if ($pagesel || (isset($this->page_box['using']) && $this->page_box['using'])) {
		if ($pagesel || $this->page==1) { $first = true; }
		else { $first = false; }
		if ($this->mirrorMargins && ($this->page % 2==0)) { $oddEven = 'E'; }
		else { $oddEven = 'O'; }
		if ($pagesel) { $psel = $pagesel; }
		else if ($this->page_box['current']) { $psel = $this->page_box['current']; }
		else { $psel = ''; }
		list($orientation,$mgl,$mgr,$mgt,$mgb,$mgh,$mgf,$hname,$fname,$bg,$resetpagenum,$pagenumstyle,$suppress,$marks,$newformat) = $this->SetPagedMediaCSS($psel, $first, $oddEven);
		if ($this->mirrorMargins && ($this->page % 2==0)) { 
			if ($hname) { $ehvalue = 1; $ehname = $hname; } else { $ehvalue = -1; }
			if ($fname) { $efvalue = 1; $efname = $fname; } else { $efvalue = -1; }
		}
		else { 
			if ($hname) { $ohvalue = 1; $ohname = $hname; } else { $ohvalue = -1; }
			if ($fname) { $ofvalue = 1; $ofname = $fname; } else { $ofvalue = -1; }
		}
		if ($resetpagenum || $pagenumstyle || $suppress) {
			$this->PageNumSubstitutions[] = array('from'=>($this->page), 'reset'=> $resetpagenum, 'type'=>$pagenumstyle, 'suppress'=>$suppress);
		}
  		// PAGED MEDIA - CROP / CROSS MARKS from @PAGE
		$this->show_marks = $marks;

		// Background color
		if (isset($bg['BACKGROUND-COLOR'])) {
			$cor = $this->ConvertColor($bg['BACKGROUND-COLOR']);
			if ($cor) { 
				$this->bodyBackgroundColor = $cor; 
			}
		}
		else { $this->bodyBackgroundColor = false; }

/*-- BACKGROUNDS --*/
		if (isset($bg['BACKGROUND-GRADIENT'])) { 
			$this->bodyBackgroundGradient = $bg['BACKGROUND-GRADIENT'];
		}
		else { $this->bodyBackgroundGradient = false; }

		// Tiling Patterns
		if (isset($bg['BACKGROUND-IMAGE']) && $bg['BACKGROUND-IMAGE']) { 
			$ret = $this->SetBackground($bg, $this->pgwidth);
			if ($ret) { $this->bodyBackgroundImage = $ret; }
		}
		else { $this->bodyBackgroundImage = false; }
/*-- END BACKGROUNDS --*/

		$this->page_box['current'] = $psel;
		$this->page_box['using'] = true;
	}
/*-- END CSS-PAGE --*/

	//Page orientation
	if(!$orientation)
		$orientation=$this->DefOrientation;
	else {
		$orientation=strtoupper(substr($orientation,0,1));
		if($orientation!=$this->DefOrientation)
			$this->OrientationChanges[$this->page]=true;
	}
	if($orientation!=$this->CurOrientation || $newformat) {

		//Change orientation
		if($orientation=='P') {
			$this->wPt=$this->fwPt;
			$this->hPt=$this->fhPt;
			$this->w=$this->fw;
			$this->h=$this->fh;
		   if (($this->forcePortraitHeaders || $this->forcePortraitMargins) && $this->DefOrientation=='P') {
			$this->tMargin = $this->orig_tMargin;
			$this->bMargin = $this->orig_bMargin;
			$this->DeflMargin = $this->orig_lMargin;
			$this->DefrMargin = $this->orig_rMargin;
			$this->margin_header = $this->orig_hMargin;
			$this->margin_footer = $this->orig_fMargin;
		   }
		   else { $resetHTMLHeadersrequired = true; }	// *HTMLHEADERS-FOOTERS*
		}
		else {
			$this->wPt=$this->fhPt;
			$this->hPt=$this->fwPt;
			$this->w=$this->fh;
			$this->h=$this->fw;
		   if (($this->forcePortraitHeaders || $this->forcePortraitMargins) && $this->DefOrientation=='P') {
			$this->tMargin = $this->orig_lMargin;
			$this->bMargin = $this->orig_rMargin;
			$this->DeflMargin = $this->orig_bMargin;
			$this->DefrMargin = $this->orig_tMargin;
			$this->margin_header = $this->orig_hMargin;
			$this->margin_footer = $this->orig_fMargin;
		   }
		   else { $resetHTMLHeadersrequired = true; }	// *HTMLHEADERS-FOOTERS*

		}
		$this->CurOrientation=$orientation;
		$this->ResetMargins();
		$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
		$this->PageBreakTrigger=$this->h-$this->bMargin;
	}

	$this->pageDim[$this->page]['w']=$this->w ;
	$this->pageDim[$this->page]['h']=$this->h ;

	$this->pageDim[$this->page]['outer_width_LR'] = isset($this->page_box['outer_width_LR']) ? $this->page_box['outer_width_LR'] : 0; 
	$this->pageDim[$this->page]['outer_width_TB'] = isset($this->page_box['outer_width_TB']) ? $this->page_box['outer_width_TB'] : 0; 
	if (!isset($this->page_box['outer_width_LR']) && !isset($this->page_box['outer_width_TB'])) {
		$this->pageDim[$this->page]['bleedMargin'] = 0;
	}
	else if ($this->bleedMargin <= $this->page_box['outer_width_LR'] && $this->bleedMargin <= $this->page_box['outer_width_TB']) {
		$this->pageDim[$this->page]['bleedMargin'] = $this->bleedMargin;
	}
	else {
		$this->pageDim[$this->page]['bleedMargin'] = min($this->page_box['outer_width_LR'], $this->page_box['outer_width_TB'])-0.01;
	}

	// If Page Margins are re-defined
	// strlen()>0 is used to pick up (integer) 0, (string) '0', or set value
	if ((strlen($mgl)>0 && $this->DeflMargin != $mgl) || (strlen($mgr)>0 && $this->DefrMargin != $mgr) || (strlen($mgt)>0 && $this->tMargin != $mgt) || (strlen($mgb)>0 && $this->bMargin != $mgb) || (strlen($mgh)>0 && $this->margin_header!=$mgh) || (strlen($mgf)>0 && $this->margin_footer!=$mgf)) {
		if (strlen($mgl)>0)  $this->DeflMargin = $mgl;
		if (strlen($mgr)>0)  $this->DefrMargin = $mgr;
		if (strlen($mgt)>0)  $this->tMargin = $mgt;
		if (strlen($mgb)>0)  $this->bMargin = $mgb;
		if (strlen($mgh)>0)  $this->margin_header=$mgh;
		if (strlen($mgf)>0)  $this->margin_footer=$mgf;
		$this->ResetMargins();
		$this->SetAutoPageBreak($this->autoPageBreak,$this->bMargin);
		$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
		$resetHTMLHeadersrequired = true; 	// *HTMLHEADERS-FOOTERS*
	}

	$this->ResetMargins();
	$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
	$this->SetAutoPageBreak($this->autoPageBreak,$this->bMargin);

	// Reset column top margin
	$this->y0 = $this->tMargin;

	$this->x=$this->lMargin;
	$this->y=$this->tMargin;
	$this->FontFamily='';

	// HEADERS AND FOOTERS
	if ($ohvalue<0 || strtoupper($ohvalue)=='OFF') { 
		$this->HTMLHeader = ''; 
		$this->headerDetails['odd'] = array(); 
		$resetHTMLHeadersrequired = true;	// *HTMLHEADERS-FOOTERS*
	}
	else if ($ohname && $ohvalue>0) {
/*-- HTMLHEADERS-FOOTERS --*/
	   if (preg_match('/^html_(.*)$/i',$ohname,$n)) {
		if (isset($this->pageHTMLheaders[$n[1]])) { $this->HTMLHeader = $this->pageHTMLheaders[$n[1]]; }
		else { $this->HTMLHeader = ''; }
		$this->headerDetails['odd'] = array(); 
		$resetHTMLHeadersrequired = true;
	   }
	   else {
/*-- END HTMLHEADERS-FOOTERS --*/
		if (isset($this->pageheaders[$ohname])) { $this->headerDetails['odd'] = $this->pageheaders[$ohname]; } 
		else if ($ohname!='_default') { $this->headerDetails['odd'] = array(); }
		$this->HTMLHeader = ''; 
/*-- HTMLHEADERS-FOOTERS --*/
		$resetHTMLHeadersrequired = false;
	   }
/*-- END HTMLHEADERS-FOOTERS --*/
	}

	if ($ehvalue<0 || strtoupper($ehvalue)=='OFF') { 
		$this->HTMLHeaderE = ''; 
		$this->headerDetails['even'] = array(); 
		$resetHTMLHeadersrequired = true;	// *HTMLHEADERS-FOOTERS*
	}
	else if ($ehname && $ehvalue>0) {
/*-- HTMLHEADERS-FOOTERS --*/
	   if (preg_match('/^html_(.*)$/i',$ehname,$n)) {
		if (isset($this->pageHTMLheaders[$n[1]])) { $this->HTMLHeaderE = $this->pageHTMLheaders[$n[1]]; } 
		else { $this->HTMLHeaderE = ''; }
		$this->headerDetails['even'] = array(); 
		$resetHTMLHeadersrequired = true;
	   }
	   else {
/*-- END HTMLHEADERS-FOOTERS --*/
		if (isset($this->pageheaders[$ehname])) { $this->headerDetails['even'] = $this->pageheaders[$ehname]; }
		else if ($ehname!='_default') { $this->headerDetails['even'] = array(); }
		$this->HTMLHeaderE = ''; 
/*-- HTMLHEADERS-FOOTERS --*/
		$resetHTMLHeadersrequired = false;
	   }
/*-- END HTMLHEADERS-FOOTERS --*/
	}

	if ($ofvalue<0 || strtoupper($ofvalue)=='OFF') { 
		$this->HTMLFooter = ''; 
		$this->footerDetails['odd'] = array(); 
		$resetHTMLHeadersrequired = true;	// *HTMLHEADERS-FOOTERS*
	}
	else if ($ofname && $ofvalue>0) {
/*-- HTMLHEADERS-FOOTERS --*/
	   if (preg_match('/^html_(.*)$/i',$ofname,$n)) {
		if (isset($this->pageHTMLfooters[$n[1]])) { $this->HTMLFooter = $this->pageHTMLfooters[$n[1]]; }
		else { $this->HTMLFooter = ''; }
		$this->footerDetails['odd'] = array(); 
		$resetHTMLHeadersrequired = true;
	   }
	   else {
/*-- END HTMLHEADERS-FOOTERS --*/
		if (isset($this->pagefooters[$ofname])) { $this->footerDetails['odd'] = $this->pagefooters[$ofname]; }
		else if ($ofname!='_default') { $this->footerDetails['odd'] = array(); }
		$this->HTMLFooter = ''; 
/*-- HTMLHEADERS-FOOTERS --*/
		$resetHTMLHeadersrequired = true;
	   }
/*-- END HTMLHEADERS-FOOTERS --*/
	}

	if ($efvalue<0 || strtoupper($efvalue)=='OFF') { 
		$this->HTMLFooterE = ''; 
		$this->footerDetails['even'] = array(); 
		$resetHTMLHeadersrequired = true;	// *HTMLHEADERS-FOOTERS*
	}
	else if ($efname && $efvalue>0) {
/*-- HTMLHEADERS-FOOTERS --*/
	   if (preg_match('/^html_(.*)$/i',$efname,$n)) {
		if (isset($this->pageHTMLfooters[$n[1]])) { $this->HTMLFooterE = $this->pageHTMLfooters[$n[1]]; } 
		else { $this->HTMLFooterE = ''; }
		$this->footerDetails['even'] = array(); 
		$resetHTMLHeadersrequired = true;
	   }
	   else {
/*-- END HTMLHEADERS-FOOTERS --*/
		if (isset($this->pagefooters[$efname])) { $this->footerDetails['even'] = $this->pagefooters[$efname]; } 
		else if ($efname!='_default') { $this->footerDetails['even'] = array(); }
		$this->HTMLFooterE = ''; 
/*-- HTMLHEADERS-FOOTERS --*/
		$resetHTMLHeadersrequired = true;
	   }
/*-- END HTMLHEADERS-FOOTERS --*/
	}
/*-- HTMLHEADERS-FOOTERS --*/
	if ($resetHTMLHeadersrequired) {
		$this->SetHTMLHeader($this->HTMLHeader );
		$this->SetHTMLHeader($this->HTMLHeaderE ,'E');
		$this->SetHTMLFooter($this->HTMLFooter );
		$this->SetHTMLFooter($this->HTMLFooterE ,'E');
	}
/*-- END HTMLHEADERS-FOOTERS --*/


	if (($this->mirrorMargins) && (($this->page)%2==0)) {	// EVEN
		$this->_setAutoHeaderHeight($this->headerDetails['even'], $this->HTMLHeaderE);
		$this->_setAutoFooterHeight($this->footerDetails['even'], $this->HTMLFooterE);
	}
	else {	// ODD or DEFAULT
		$this->_setAutoHeaderHeight($this->headerDetails['odd'], $this->HTMLHeader);
		$this->_setAutoFooterHeight($this->footerDetails['odd'], $this->HTMLFooter);
	}
	// Reset column top margin
	$this->y0 = $this->tMargin;

	$this->x=$this->lMargin;
	$this->y=$this->tMargin;
}



function _setAutoHeaderHeight(&$det, &$htmlh) {
  if ($this->setAutoTopMargin=='pad') {
	if ($htmlh['h']) { $h = $htmlh['h']; }
	else if ($det) { $h = $this->_getHFHeight($det,'H'); }
	else { $h = 0; }
	$this->tMargin = $this->margin_header + $h + $this->orig_tMargin;
  }
  else if ($this->setAutoTopMargin=='stretch') {
	if ($htmlh['h']) { $h = $htmlh['h']; }
	else if ($det) { $h = $this->_getHFHeight($det,'H'); }
	else { $h = 0; }
	$this->tMargin = max($this->orig_tMargin, $this->margin_header + $h + $this->autoMarginPadding);
  }
}


function _setAutoFooterHeight(&$det, &$htmlf) {
  if ($this->setAutoBottomMargin=='pad') {
	if ($htmlf['h']) { $h = $htmlf['h']; }
	else if ($det) { $h = $this->_getHFHeight($det,'F'); }
	else { $h = 0; }
	$this->bMargin = $this->margin_footer + $h + $this->orig_bMargin;
	$this->PageBreakTrigger=$this->h-$this->bMargin ;
  }
  else if ($this->setAutoBottomMargin=='stretch') {
	if ($htmlf['h']) { $h = $htmlf['h']; }
	else if ($det) { $h = $this->_getHFHeight($det,'F'); }
	else { $h = 0; }
	$this->bMargin = max($this->orig_bMargin, $this->margin_footer + $h + $this->autoMarginPadding);
	$this->PageBreakTrigger=$this->h-$this->bMargin ;
  }
}

function _getHFHeight(&$det,$end) {
	$h = 0;
	if(count($det)) {
		foreach(array('L','C','R') AS $pos) {
		  if (isset($det[$pos]['content']) && $det[$pos]['content']) {
			if (isset($det[$pos]['font-size']) && $det[$pos]['font-size']) { $hfsz = $det[$pos]['font-size']; }
			else { $hfsz = $this->default_font_size; }
			$h = max($h,$hfsz/_MPDFK);
		  }
		}
		if ($det['line'] && $end=='H') { $h += $h/_MPDFK*$this->header_line_spacing; }
		else if ($det['line'] && $end=='F') { $h += $h/_MPDFK*$this->footer_line_spacing; }
   	}
	return $h;
}


function _endpage() {
/*-- CSS-IMAGE-FLOAT --*/
	$this->printfloatbuffer();
/*-- END CSS-IMAGE-FLOAT --*/

	if($this->visibility!='visible')
		$this->SetVisibility('visible');
	$this->EndLayer();	// mPDF 5.6.01
	//End of page contents
	$this->state=1;
}

function _newobj($obj_id=false,$onlynewobj=false) {
		if (!$obj_id) {
			$obj_id = ++$this->n;
		}
		//Begin a new object
		if (!$onlynewobj) {
			$this->offsets[$obj_id] = strlen($this->buffer);
			$this->_out($obj_id.' 0 obj');
			$this->_current_obj_id = $obj_id; // for later use with encryption
		}
}

function _dounderline($x,$y,$txt) {
	// Now print line exactly where $y secifies - called from Text() and Cell() - adjust  position there
	// WORD SPACING
      $w =($this->GetStringWidth($txt)*_MPDFK) + ($this->charspacing * mb_strlen( $txt, $this->mb_enc )) 
		 + ( $this->ws * mb_substr_count( $txt, ' ', $this->mb_enc ));
	//Draw a line
	return sprintf('%.3F %.3F m %.3F %.3F l S',$x*_MPDFK,($this->h-$y)*_MPDFK,($x*_MPDFK)+$w,($this->h-$y)*_MPDFK);
}


function _imageError($file, $firsttime, $msg) {
	// Save re-trying image URL's which have already failed
	$this->failedimages[$file] = true;
	if ($firsttime && ($this->showImageErrors || $this->debug)) {
			$this->Error("IMAGE Error (".$file."): ".$msg);
	}
	return false;
}


function _getImage(&$file, $firsttime=true, $allowvector=true, $orig_srcpath=false) { 
	// firsttime i.e. whether to add to this->images - use false when calling iteratively
	// Image Data passed directly as var:varname
	if (preg_match('/var:\s*(.*)/',$file, $v)) { 
		$data = $this->$v[1];
		$file = md5($data);
	}
	// mPDF 5.5.13
	if (preg_match('/data:image\/(gif|jpeg|png);base64,(.*)/',$file, $v)) { 
		$type = $v[1];
		$data = base64_decode($v[2]);
		$file = md5($data);
	}

	// mPDF 5.6.02
	if ($firsttime && $file && substr($file,0,5)!='data:') { $file = urlencode_part($file); }
	if ($firsttime && $orig_srcpath && substr($orig_srcpath,0,5)!='data:') { $orig_srcpath = urlencode_part($orig_srcpath); }

	$ppUx = 0;
	if ($orig_srcpath && isset($this->images[$orig_srcpath])) { $file=$orig_srcpath; return $this->images[$orig_srcpath]; }
	if (isset($this->images[$file])) { return $this->images[$file]; }
	else if ($orig_srcpath && isset($this->formobjects[$orig_srcpath])) { $file=$orig_srcpath; return $this->formobjects[$file]; }
	else if (isset($this->formobjects[$file])) { return $this->formobjects[$file]; }
	// Save re-trying image URL's which have already failed
	else if ($firsttime && isset($this->failedimages[$file])) { return $this->_imageError($file, $firsttime, ''); } 
	if (empty($data)) {
		$type = '';
		$data = '';

 		if ($orig_srcpath && $this->basepathIsLocal && $check = @fopen($orig_srcpath,"rb")) {
			fclose($check); 
			$file=$orig_srcpath;
			$data = file_get_contents($file);
			$type = $this->_imageTypeFromString($data);
		}
		if (!$data && $check = @fopen($file,"rb")) {
			fclose($check); 
			$data = file_get_contents($file);
			$type = $this->_imageTypeFromString($data);
		}
		if ((!$data || !$type) && !ini_get('allow_url_fopen') ) {	// only worth trying if remote file and !ini_get('allow_url_fopen')
			$this->file_get_contents_by_socket($file, $data);	// needs full url?? even on local (never needed for local)
			if ($data) { $type = $this->_imageTypeFromString($data); }
		}
		if ((!$data || !$type) && !ini_get('allow_url_fopen') && function_exists("curl_init")) {
			$this->file_get_contents_by_curl($file, $data);		// needs full url?? even on local (never needed for local)
			if ($data) { $type = $this->_imageTypeFromString($data); }
		}

	}
	if (!$data) { return $this->_imageError($file, $firsttime, 'Could not find image file'); }
	if (empty($type)) { $type = $this->_imageTypeFromString($data); }	
	if (($type == 'wmf' || $type == 'svg') && !$allowvector) { return $this->_imageError($file, $firsttime, 'WMF or SVG image file not supported in this context'); }

	// SVG
	if ($type == 'svg') {
		if (!class_exists('SVG', false)) { include(_MPDF_PATH .'classes/svg.php'); }
		$svg = new SVG($this);
		$family=$this->FontFamily;
		$style=$this->FontStyle;
		$size=$this->FontSizePt;
		$info = $svg->ImageSVG($data);
		//Restore font
		if($family) $this->SetFont($family,$style,$size,false);
		if (!$info) { return $this->_imageError($file, $firsttime, 'Error parsing SVG file'); }
		$info['type']='svg';
		$info['i']=count($this->formobjects)+1;
		$this->formobjects[$file]=$info;
		return $info;
	}

	// JPEG
	if ($type == 'jpeg' || $type == 'jpg') {
		$hdr = $this->_jpgHeaderFromString($data);
		if (!$hdr) { return $this->_imageError($file, $firsttime, 'Error parsing JPG header'); }
		$a = $this->_jpgDataFromHeader($hdr);
		$j = strpos($data,'JFIF');
		if ($j) { 
			//Read resolution
			$unitSp=ord(substr($data,($j+7),1));
			if ($unitSp > 0) {
				$ppUx=$this->_twobytes2int(substr($data,($j+8),2));	// horizontal pixels per meter, usually set to zero
				if ($unitSp == 2) {	// = dots per cm (if == 1 set as dpi)
					$ppUx=round($ppUx/10 *25.4);
				}
			}
		}
		if ($a[2] == 'DeviceCMYK' && (($this->PDFA && $this->restrictColorSpace!=3) || $this->restrictColorSpace==2)) {
			// convert to RGB image
			if (!function_exists("gd_info")) { $this->Error("JPG image may not use CMYK color space (".$file.")."); }
			if ($this->PDFA && !$this->PDFAauto) { $this->PDFAXwarnings[] = "JPG image may not use CMYK color space - ".$file." - (Image converted to RGB. NB This will alter the colour profile of the image.)"; }
			$im = @imagecreatefromstring($data);
			if ($im) {
				$tempfile = _MPDF_TEMP_PATH.'_tempImgPNG'.RAND(1,10000).'.png';
				imageinterlace($im, false);
				$check = @imagepng($im, $tempfile);
				if (!$check) { return $this->_imageError($file, $firsttime, 'Error creating temporary file ('.$tempfile.') whilst using GD library to parse JPG(CMYK) image'); }
				$info = $this->_getImage($tempfile, false);
				if (!$info) { return $this->_imageError($file, $firsttime, 'Error parsing temporary file ('.$tempfile.') created with GD library to parse JPG(CMYK) image'); }
				imagedestroy($im);
				unlink($tempfile);
				$info['type']='jpg';
				if ($firsttime) {
					$info['i']=count($this->images)+1;
					$this->images[$file]=$info;
				}
				return $info;
			}
			else { return $this->_imageError($file, $firsttime, 'Error creating GD image file from JPG(CMYK) image'); }
		}
		else if ($a[2] == 'DeviceRGB' && ($this->PDFX || $this->restrictColorSpace==3)) {
			// Convert to CMYK image stream - nominally returned as type='png'
			$info = $this->_convImage($data, $a[2], 'DeviceCMYK', $a[0], $a[1], $ppUx, false);
			if (($this->PDFA && !$this->PDFAauto) || ($this->PDFX && !$this->PDFXauto)) { $this->PDFAXwarnings[] = "JPG image may not use RGB color space - ".$file." - (Image converted to CMYK. NB This will alter the colour profile of the image.)"; }
		}
		else if (($a[2] == 'DeviceRGB' || $a[2] == 'DeviceCMYK') && $this->restrictColorSpace==1) {
			// Convert to Grayscale image stream - nominally returned as type='png'
			$info = $this->_convImage($data, $a[2], 'DeviceGray', $a[0], $a[1], $ppUx, false);
		}
		else {
			$info = array('w'=>$a[0],'h'=>$a[1],'cs'=>$a[2],'bpc'=>$a[3],'f'=>'DCTDecode','data'=>$data, 'type'=>'jpg');
			if ($ppUx) { $info['set-dpi'] = $ppUx; }
		}
		if (!$info) { return $this->_imageError($file, $firsttime, 'Error parsing or converting JPG image'); }

		if ($firsttime) {
			$info['i']=count($this->images)+1;
			$this->images[$file]=$info;
		}
		return $info;
	}

	// PNG
	else if ($type == 'png') {
		//Check signature
		if(substr($data,0,8)!=chr(137).'PNG'.chr(13).chr(10).chr(26).chr(10)) { 
			return $this->_imageError($file, $firsttime, 'Error parsing PNG identifier'); 
		}
		//Read header chunk
		if(substr($data,12,4)!='IHDR') { 
			return $this->_imageError($file, $firsttime, 'Incorrect PNG file (no IHDR block found)'); 
		}

		$w=$this->_fourbytes2int(substr($data,16,4));
		$h=$this->_fourbytes2int(substr($data,20,4));
		$bpc=ord(substr($data,24,1));
		$errpng = false;
		$pngalpha = false; 
		if($bpc>8) { $errpng = 'not 8-bit depth'; }
		$ct=ord(substr($data,25,1));
		if($ct==0) { $colspace='DeviceGray'; }
		elseif($ct==2) { $colspace='DeviceRGB'; }
		elseif($ct==3) { $colspace='Indexed'; }
		elseif($ct==4) { $colspace='DeviceGray';  $errpng = 'alpha channel'; $pngalpha = true; }
		else { $colspace='DeviceRGB'; $errpng = 'alpha channel'; $pngalpha = true; } 
		if(ord(substr($data,26,1))!=0) { $errpng = 'compression method'; }
		if(ord(substr($data,27,1))!=0) { $errpng = 'filter method'; }
		if(ord(substr($data,28,1))!=0) { $errpng = 'interlaced file'; }
		$j = strpos($data,'pHYs');
		if ($j) { 
			//Read resolution
			$unitSp=ord(substr($data,($j+12),1));
			if ($unitSp == 1) {
				$ppUx=$this->_fourbytes2int(substr($data,($j+4),4));	// horizontal pixels per meter, usually set to zero
				$ppUx=round($ppUx/1000 *25.4);
			}
		}
		if (($colspace == 'DeviceRGB' || $colspace == 'Indexed') && ($this->PDFX || $this->restrictColorSpace==3)) {
			// Convert to CMYK image stream - nominally returned as type='png'
			$info = $this->_convImage($data, $colspace, 'DeviceCMYK', $w, $h, $ppUx, $pngalpha);
			if (($this->PDFA && !$this->PDFAauto) || ($this->PDFX && !$this->PDFXauto)) { $this->PDFAXwarnings[] = "PNG image may not use RGB color space - ".$file." - (Image converted to CMYK. NB This will alter the colour profile of the image.)"; }
		}
		else if (($colspace == 'DeviceRGB' || $colspace == 'Indexed') && $this->restrictColorSpace==1) {
			// Convert to Grayscale image stream - nominally returned as type='png'
			$info = $this->_convImage($data, $colspace, 'DeviceGray', $w, $h, $ppUx, $pngalpha);
		}
		else if (($this->PDFA || $this->PDFX) && $pngalpha) {
			// Remove alpha channel
			if ($this->restrictColorSpace==1) {	// Grayscale
				$info = $this->_convImage($data, $colspace, 'DeviceGray', $w, $h, $ppUx, $pngalpha);
			}
			else if ($this->restrictColorSpace==3) {	// CMYK
				$info = $this->_convImage($data, $colspace, 'DeviceCMYK', $w, $h, $ppUx, $pngalpha);
			}
			else if ($this->PDFA ) {	// RGB
				$info = $this->_convImage($data, $colspace, 'DeviceRGB', $w, $h, $ppUx, $pngalpha);
			}
			if (($this->PDFA && !$this->PDFAauto) || ($this->PDFX && !$this->PDFXauto)) { $this->PDFAXwarnings[] = "Transparency (alpha channel) not permitted in PDFA or PDFX files - ".$file." - (Image converted to one without transparency.)"; }
		}
		else if ($errpng || $pngalpha) {
			if (function_exists('gd_info')) { $gd = gd_info(); }
			else {$gd = array(); }
			if (!isset($gd['PNG Support'])) { return $this->_imageError($file, $firsttime, 'GD library required for PNG image ('.$errpng.')'); }
			$im = imagecreatefromstring($data);
			if (!$im) { return $this->_imageError($file, $firsttime, 'Error creating GD image from PNG file ('.$errpng.')'); }
			$w = imagesx($im);
			$h = imagesy($im);
			if ($im) {
			   $tempfile = _MPDF_TEMP_PATH.'_tempImgPNG'.RAND(1,10000).'.png';
			   // Alpha channel set
			   if ($pngalpha) {
				if ($this->PDFA) { $this->Error("PDFA1-b does not permit images with alpha channel transparency (".$file.")."); }
				$imgalpha = imagecreate($w, $h);
				// generate gray scale pallete
				for ($c = 0; $c < 256; ++$c) { ImageColorAllocate($imgalpha, $c, $c, $c); }
				// extract alpha channel
				$gammacorr = 2.2;	// gamma correction
				for ($xpx = 0; $xpx < $w; ++$xpx) {
					for ($ypx = 0; $ypx < $h; ++$ypx) {
						//$colorindex = imagecolorat($im, $xpx, $ypx);
						//$col = imagecolorsforindex($im, $colorindex);
						//$gamma2 = (pow((((127 - $col['alpha']) * 255 / 127) / 255), $gammacorr) * 255);
						$alpha = (imagecolorat($im, $xpx, $ypx) & 0x7F000000) >> 24;
						if ($alpha < 127) {
							if ($alpha==0) { $gamma = 255; }
							else
								$gamma = (pow((((127 - $alpha) * 255 / 127) / 255), $gammacorr) * 255);
							imagesetpixel($imgalpha, $xpx, $ypx, $gamma);
						}
					}
				}
				// create temp alpha file
	 		 	$tempfile_alpha = _MPDF_TEMP_PATH.'_tempMskPNG'.RAND(1,10000).'.png';
				if (!is_writable($tempfile_alpha)) { 
					ob_start(); 
					$check = @imagepng($imgalpha);
					if (!$check) { return $this->_imageError($file, $firsttime, 'Error creating temporary image object whilst using GD library to parse PNG image'); }
					imagedestroy($imgalpha);
					$this->_tempimg = ob_get_contents();
					$this->_tempimglnk = 'var:_tempimg';
					ob_end_clean();
					// extract image without alpha channel
					$imgplain = imagecreatetruecolor($w, $h);
					imagecopy($imgplain, $im, 0, 0, 0, 0, $w, $h);
					// create temp image file
					$minfo = $this->_getImage($this->_tempimglnk, false);
					if (!$minfo) { return $this->_imageError($file, $firsttime, 'Error parsing temporary file image object created with GD library to parse PNG image'); }
					ob_start(); 
					$check = @imagepng($imgplain);
					if (!$check) { return $this->_imageError($file, $firsttime, 'Error creating temporary image object whilst using GD library to parse PNG image'); }
					$this->_tempimg = ob_get_contents();
					$this->_tempimglnk = 'var:_tempimg';
					ob_end_clean();
					$info = $this->_getImage($this->_tempimglnk, false);
					if (!$info) { return $this->_imageError($file, $firsttime, 'Error parsing temporary file image object created with GD library to parse PNG image'); }
					imagedestroy($imgplain);
					$imgmask = count($this->images)+1;
					$minfo['cs'] = 'DeviceGray';
					$minfo['i']=$imgmask ;
					$this->images[$tempfile_alpha] = $minfo;

				}
				else {
					$check = @imagepng($imgalpha, $tempfile_alpha);
					if (!$check) { return $this->_imageError($file, $firsttime, 'Failed to create temporary image file ('.$tempfile_alpha.') parsing PNG image with alpha channel ('.$errpng.')'); }
					imagedestroy($imgalpha);

					// extract image without alpha channel
					$imgplain = imagecreatetruecolor($w, $h);
					imagecopy($imgplain, $im, 0, 0, 0, 0, $w, $h);

					// create temp image file
					$check = @imagepng($imgplain, $tempfile);
					if (!$check) { return $this->_imageError($file, $firsttime, 'Failed to create temporary image file ('.$tempfile.') parsing PNG image with alpha channel ('.$errpng.')'); }
					imagedestroy($imgplain);
					// embed mask image
					$minfo = $this->_getImage($tempfile_alpha, false);
					unlink($tempfile_alpha);
					if (!$minfo) { return $this->_imageError($file, $firsttime, 'Error parsing temporary file ('.$tempfile_alpha.') created with GD library to parse PNG image'); }
					$imgmask = count($this->images)+1;
					$minfo['cs'] = 'DeviceGray';
					$minfo['i']=$imgmask ;
					$this->images[$tempfile_alpha] = $minfo;
					// embed image, masked with previously embedded mask
					$info = $this->_getImage($tempfile, false);
					unlink($tempfile);
					if (!$info) { return $this->_imageError($file, $firsttime, 'Error parsing temporary file ('.$tempfile.') created with GD library to parse PNG image'); }

				}
				$info['masked'] = $imgmask;
				if ($ppUx) { $info['set-dpi'] = $ppUx; }
				$info['type']='png';
				if ($firsttime) {
					$info['i']=count($this->images)+1;
					$this->images[$file]=$info;
				}
				return $info;
			   }
			   else { 	// No alpha/transparency set
				imagealphablending($im, false);
				imagesavealpha($im, false); 
				imageinterlace($im, false);
				if (!is_writable($tempfile)) { 
					ob_start(); 
					$check = @imagepng($im);
					if (!$check) { return $this->_imageError($file, $firsttime, 'Error creating temporary image object whilst using GD library to parse PNG image'); }
					$this->_tempimg = ob_get_contents();
					$this->_tempimglnk = 'var:_tempimg';
					ob_end_clean();
					$info = $this->_getImage($this->_tempimglnk, false);
					if (!$info) { return $this->_imageError($file, $firsttime, 'Error parsing temporary file image object created with GD library to parse PNG image'); }
					imagedestroy($im);
				}
				else {
					$check = @imagepng($im, $tempfile );
					if (!$check) { return $this->_imageError($file, $firsttime, 'Failed to create temporary image file ('.$tempfile.') parsing PNG image ('.$errpng.')'); }
					imagedestroy($im);
					$info = $this->_getImage($tempfile, false) ;
					unlink($tempfile ); 
					if (!$info) { return $this->_imageError($file, $firsttime, 'Error parsing temporary file ('.$tempfile.') created with GD library to parse PNG image'); }
				}
				if ($ppUx) { $info['set-dpi'] = $ppUx; }
				$info['type']='png';
				if ($firsttime) {
					$info['i']=count($this->images)+1;
					$this->images[$file]=$info;
				}
				return $info;
			   }
			}
		}

		else {
			$parms='/DecodeParms <</Predictor 15 /Colors '.($ct==2 ? 3 : 1).' /BitsPerComponent '.$bpc.' /Columns '.$w.'>>';
			//Scan chunks looking for palette, transparency and image data
			$pal='';
			$trns='';
			$pngdata='';
			$p = 33;
			do {
				$n=$this->_fourbytes2int(substr($data,$p,4));	$p += 4;
				$type=substr($data,$p,4);	$p += 4;
				if($type=='PLTE') {
					//Read palette
					$pal=substr($data,$p,$n);	$p += $n;
					$p += 4;
				}
				elseif($type=='tRNS') {
					//Read transparency info
					$t=substr($data,$p,$n);	$p += $n;
					if($ct==0) $trns=array(ord(substr($t,1,1)));
					elseif($ct==2) $trns=array(ord(substr($t,1,1)),ord(substr($t,3,1)),ord(substr($t,5,1)));
					else
					{
						$pos=strpos($t,chr(0));
						if(is_int($pos)) $trns=array($pos);
					}
					$p += 4;
				}
				elseif($type=='IDAT') {
					$pngdata.=substr($data,$p,$n);	$p += $n;
					$p += 4;
				}
				elseif($type=='IEND') { break; }
				else if (preg_match('/[a-zA-Z]{4}/',$type)) { $p += $n+4; }
				else { return $this->_imageError($file, $firsttime, 'Error parsing PNG image data'); }
			}
			while($n);
			if (!$pngdata) { return $this->_imageError($file, $firsttime, 'Error parsing PNG image data - no IDAT data found'); }
			if($colspace=='Indexed' and empty($pal)) { return $this->_imageError($file, $firsttime, 'Error parsing PNG image data - missing colour palette'); }
			$info = array('w'=>$w,'h'=>$h,'cs'=>$colspace,'bpc'=>$bpc,'f'=>'FlateDecode','parms'=>$parms,'pal'=>$pal,'trns'=>$trns,'data'=>$pngdata);
			$info['type']='png';
			if ($ppUx) { $info['set-dpi'] = $ppUx; }
		}

		if (!$info) { return $this->_imageError($file, $firsttime, 'Error parsing or converting PNG image'); }

		if ($firsttime) {
			$info['i']=count($this->images)+1;
			$this->images[$file]=$info;
		}
		return $info;
	}

	// GIF
	else if ($type == 'gif') {
	if (function_exists('gd_info')) { $gd = gd_info(); }
		else {$gd = array(); }
		if (isset($gd['GIF Read Support']) && $gd['GIF Read Support']) {
			$im = @imagecreatefromstring($data);
			if ($im) {
				$tempfile = _MPDF_TEMP_PATH.'_tempImgPNG'.RAND(1,10000).'.png';
				imagealphablending($im, false);
				imagesavealpha($im, false); 
				imageinterlace($im, false);
				if (!is_writable($tempfile)) { 
					ob_start(); 
					$check = @imagepng($im);
					if (!$check) { return $this->_imageError($file, $firsttime, 'Error creating temporary image object whilst using GD library to parse GIF image'); }
					$this->_tempimg = ob_get_contents();
					$this->_tempimglnk = 'var:_tempimg';
					ob_end_clean();
					$info = $this->_getImage($this->_tempimglnk, false);
					if (!$info) { return $this->_imageError($file, $firsttime, 'Error parsing temporary file image object created with GD library to parse GIF image'); }
					imagedestroy($im);
				}
				else {
					$check = @imagepng($im, $tempfile);
					if (!$check) { return $this->_imageError($file, $firsttime, 'Error creating temporary file ('.$tempfile.') whilst using GD library to parse GIF image'); }
					$info = $this->_getImage($tempfile, false);
					if (!$info) { return $this->_imageError($file, $firsttime, 'Error parsing temporary file ('.$tempfile.') created with GD library to parse GIF image'); }
					imagedestroy($im);
					unlink($tempfile);
				}
				$info['type']='gif';
				if ($firsttime) {
					$info['i']=count($this->images)+1;
					$this->images[$file]=$info;
				}
				return $info;
			}
			else { return $this->_imageError($file, $firsttime, 'Error creating GD image file from GIF image'); }
		}

		if (!class_exists('gif', false)) { 
			include_once(_MPDF_PATH.'classes/gif.php'); 
		}
		$gif=new CGIF();

		$h=0;
		$w=0;
		$gif->loadFile($data, 0);

		if(isset($gif->m_img->m_gih->m_bLocalClr) && $gif->m_img->m_gih->m_bLocalClr) {
			$nColors = $gif->m_img->m_gih->m_nTableSize;
			$pal = $gif->m_img->m_gih->m_colorTable->toString();
			if($bgColor != -1) {
				$bgColor = $gif->m_img->m_gih->m_colorTable->colorIndex($bgColor);
			}
			$colspace='Indexed';
		} elseif(isset($gif->m_gfh->m_bGlobalClr) && $gif->m_gfh->m_bGlobalClr) {
			$nColors = $gif->m_gfh->m_nTableSize;
			$pal = $gif->m_gfh->m_colorTable->toString();
			if((isset($bgColor)) and $bgColor != -1) {
				$bgColor = $gif->m_gfh->m_colorTable->colorIndex($bgColor);
			}
			$colspace='Indexed';
		} else {
			$nColors = 0;
			$bgColor = -1;
			$colspace='DeviceGray';
			$pal='';
		}

		$trns='';
		if(isset($gif->m_img->m_bTrans) && $gif->m_img->m_bTrans && ($nColors > 0)) {
			$trns=array($gif->m_img->m_nTrans);
		}
		$gifdata=$gif->m_img->m_data;
		$w=$gif->m_gfh->m_nWidth;
		$h=$gif->m_gfh->m_nHeight;
		$gif->ClearData();

		if($colspace=='Indexed' and empty($pal)) {
			return $this->_imageError($file, $firsttime, 'Error parsing GIF image - missing colour palette'); 
		}
		if ($this->compress) {
			$gifdata=gzcompress($gifdata);
			$info = array( 'w'=>$w, 'h'=>$h, 'cs'=>$colspace, 'bpc'=>8, 'f'=>'FlateDecode', 'pal'=>$pal, 'trns'=>$trns, 'data'=>$gifdata);
		} 
		else {
			$info = array( 'w'=>$w, 'h'=>$h, 'cs'=>$colspace, 'bpc'=>8, 'pal'=>$pal, 'trns'=>$trns, 'data'=>$gifdata);
		} 
		$info['type']='gif';
		if ($firsttime) {
			$info['i']=count($this->images)+1;
			$this->images[$file]=$info;
		}
		return $info;
	}

/*-- IMAGES-BMP --*/
	// BMP (Windows Bitmap)
	else if ($type == 'bmp') {
		if (!class_exists('bmp', false)) { include(_MPDF_PATH.'classes/bmp.php'); }
		if (empty($this->bmp)) { $this->bmp = new bmp($this); }
		$info = $this->bmp->_getBMPimage($data, $file);
		if (isset($info['error'])) {
			return $this->_imageError($file, $firsttime, $info['error']); 
		}
		if ($firsttime) {
			$info['i']=count($this->images)+1;
			$this->images[$file]=$info;
		}
		return $info;
	}
/*-- END IMAGES-BMP --*/
/*-- IMAGES-WMF --*/
	// WMF
	else if ($type == 'wmf') {
		if (!class_exists('wmf', false)) { include(_MPDF_PATH.'classes/wmf.php'); }
		if (empty($this->wmf)) { $this->wmf = new wmf($this); }
		$wmfres = $this->wmf->_getWMFimage($data);
	  if ($wmfres[0]==0) { 
		if ($wmfres[1]) { return $this->_imageError($file, $firsttime, $wmfres[1]); }
		return $this->_imageError($file, $firsttime, 'Error parsing WMF image'); 
	  }
	  $info = array('x'=>$wmfres[2][0],'y'=>$wmfres[2][1],'w'=>$wmfres[3][0],'h'=>$wmfres[3][1],'data'=>$wmfres[1]);
	  $info['i']=count($this->formobjects)+1;
	  $info['type']='wmf';
	  $this->formobjects[$file]=$info;
	  return $info;
	}
/*-- END IMAGES-WMF --*/

	// UNKNOWN TYPE - try GD imagecreatefromstring
	else {
		if (function_exists('gd_info')) { $gd = gd_info(); }
		else {$gd = array(); }
		if (isset($gd['PNG Support']) && $gd['PNG Support']) {
			$im = @imagecreatefromstring($data);
			if (!$im) { return $this->_imageError($file, $firsttime, 'Error parsing image file - image type not recognised, and not supported by GD imagecreate'); }
			$tempfile = _MPDF_TEMP_PATH.'_tempImgPNG'.RAND(1,10000).'.png';
			imagealphablending($im, false);
			imagesavealpha($im, false); 
			imageinterlace($im, false);
			$check = @imagepng($im, $tempfile);
			if (!$check) { return $this->_imageError($file, $firsttime, 'Error creating temporary file ('.$tempfile.') whilst using GD library to parse unknown image type'); }
			$info = $this->_getImage($tempfile, false);
			imagedestroy($im);
			unlink($tempfile);
			if (!$info) { return $this->_imageError($file, $firsttime, 'Error parsing temporary file ('.$tempfile.') created with GD library to parse unknown image type'); }
			$info['type']='png';
			if ($firsttime) {
				$info['i']=count($this->images)+1;
				$this->images[$file]=$info;
			}
			return $info;
		}
	}

	return $this->_imageError($file, $firsttime, 'Error parsing image file - image type not recognised'); 
}
//==============================================================
function _convImage(&$data, $colspace, $targetcs, $w, $h, $dpi, $mask) {
	if ($this->PDFA || $this->PDFX) { $mask=false; }
	$im = @imagecreatefromstring($data);
	$info = array();
	if ($im) {
		$imgdata = '';
		$mimgdata = '';
		$minfo = array();
		//Read transparency info
		$trns=array();
		$trnsrgb = false;
		if (!$this->PDFA && !$this->PDFX) { 
		   $p = strpos($data,'tRNS');
		   if ($p) { 
			$n=$this->_fourbytes2int(substr($data,($p-4),4));
			$t = substr($data,($p+4),$n);	
			if ($colspace=='DeviceGray') { 
				$trns=array(ord(substr($t,1,1))); 
				$trnsrgb = array($trns[0],$trns[0],$trns[0]);
			}
			else if ($colspace=='DeviceRGB') { 
				$trns=array(ord(substr($t,1,1)),ord(substr($t,3,1)),ord(substr($t,5,1))); 
				$trnsrgb = $trns;
				if ($targetcs=='DeviceCMYK') {
					$col = $this->rgb2cmyk(array(3,$trns[0],$trns[1],$trns[2]));
					$c1 = intval($col[1]*2.55);
					$c2 = intval($col[2]*2.55);
					$c3 = intval($col[3]*2.55);
					$c4 = intval($col[4]*2.55);
					$trns = array($c1,$c2,$c3,$c4);
				}
				else if ($targetcs=='DeviceGray') {
					$c = intval(($trns[0] * .21) + ($trns[1] * .71) + ($trns[2] * .07));
					$trns = array($c);
				}
			}
			else {	// Indexed
				$pos = strpos($t,chr(0));
				if (is_int($pos)) {
					$pal = imagecolorsforindex($im, $pos);
					$r = $pal['red'];
					$g = $pal['green'];
					$b = $pal['blue'];
					$trns=array($r,$g,$b);	// ****
					$trnsrgb = $trns;
					if ($targetcs=='DeviceCMYK') {
						$col = $this->rgb2cmyk(array(3,$r,$g,$b));
						$c1 = intval($col[1]*2.55);
						$c2 = intval($col[2]*2.55);
						$c3 = intval($col[3]*2.55);
						$c4 = intval($col[4]*2.55);
						$trns = array($c1,$c2,$c3,$c4);
					}
					else if ($targetcs=='DeviceGray') {
						$c = intval(($r * .21) + ($g * .71) + ($b * .07));
						$trns = array($c);
					}
				}
			}
		   }
		}
		for ($i = 0; $i < $h; $i++) {
			for ($j = 0; $j < $w; $j++) {
				$rgb = imagecolorat($im, $j, $i);
				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;
				if ($colspace=='Indexed') {
					$pal = imagecolorsforindex($im, $rgb);
					$r = $pal['red'];
					$g = $pal['green'];
					$b = $pal['blue'];
				}

				if ($targetcs=='DeviceCMYK') {
					$col = $this->rgb2cmyk(array(3,$r,$g,$b));
					$c1 = intval($col[1]*2.55);
					$c2 = intval($col[2]*2.55);
					$c3 = intval($col[3]*2.55);
					$c4 = intval($col[4]*2.55);
					if ($trnsrgb) {
						// original pixel was not set as transparent but processed color does match
						if ($trnsrgb!=array($r,$g,$b) && $trns==array($c1,$c2,$c3,$c4)) {
							if ($c4==0) { $c4=1; } else { $c4--; }
						}
					}
					$imgdata .= chr($c1).chr($c2).chr($c3).chr($c4);
				}
				else if ($targetcs=='DeviceGray') {
					$c = intval(($r * .21) + ($g * .71) + ($b * .07));
					if ($trnsrgb) {
						// original pixel was not set as transparent but processed color does match
						if ($trnsrgb!=array($r,$g,$b) && $trns==array($c)) {
							if ($c==0) { $c=1; } else { $c--; }
						}
					}
					$imgdata .= chr($c);
				}
				else if ($targetcs=='DeviceRGB') {
					$imgdata .= chr($r).chr($g).chr($b);
				}
				if ($mask) {
					$col = imagecolorsforindex($im, $rgb);
					$gammacorr = 2.2;	// gamma correction
					$gamma = intval((pow((((127 - $col['alpha']) * 255 / 127) / 255), $gammacorr) * 255));
					$mimgdata .= chr($gamma);
				}
			}
		}

		if ($targetcs=='DeviceGray') { $ncols = 1; }
		else if ($targetcs=='DeviceRGB') { $ncols = 3; }
		else if ($targetcs=='DeviceCMYK') { $ncols = 4; }

		$imgdata = gzcompress($imgdata);
		$info = array('w'=>$w,'h'=>$h,'cs'=>$targetcs,'bpc'=>8,'f'=>'FlateDecode','data'=>$imgdata, 'type'=>'png',
			'parms'=>'/DecodeParms <</Colors '.$ncols.' /BitsPerComponent 8 /Columns '.$w.'>>');
		if ($dpi) { $info['set-dpi'] = $dpi; }
		if ($mask) { 
			$mimgdata = gzcompress($mimgdata); 
			$minfo = array('w'=>$w,'h'=>$h,'cs'=>'DeviceGray','bpc'=>8,'f'=>'FlateDecode','data'=>$mimgdata, 'type'=>'png',
			'parms'=>'/DecodeParms <</Colors '.$ncols.' /BitsPerComponent 8 /Columns '.$w.'>>');
			if ($dpi) { $minfo['set-dpi'] = $dpi; }
			$tempfile = '_tempImgPNG'.RAND(1,10000).'.png';
			$imgmask = count($this->images)+1;
			$minfo['i']=$imgmask ;
			$this->images[$tempfile] = $minfo;
			$info['masked'] = $imgmask;
		}
		else if ($trns) { $info['trns'] = $trns; }
		imagedestroy($im);
	}
	return $info;
}




function _fourbytes2int($s) {
	//Read a 4-byte integer from string
	return (ord($s[0])<<24) + (ord($s[1])<<16) + (ord($s[2])<<8) + ord($s[3]);
}

function _twobytes2int($s) {
	//Read a 2-byte integer from string
	return (ord(substr($s, 0, 1))<<8) + ord(substr($s, 1, 1));
}

function _jpgHeaderFromString(&$data) {
	$p = 4;
	$p += $this->_twobytes2int(substr($data, $p, 2));	// Length of initial marker block
	$marker = substr($data, $p, 2);
	while($marker != chr(255).chr(192) && $marker != chr(255).chr(194) && $p<strlen($data)) {
		// Start of frame marker (FFC0) or (FFC2) mPDF 4.4.004
		$p += ($this->_twobytes2int(substr($data, $p+2, 2))) + 2;	// Length of marker block
		$marker = substr($data, $p, 2);
	}
	if ($marker != chr(255).chr(192) && $marker != chr(255).chr(194)) { return false; }
	return substr($data, $p+2, 10);
}

function _jpgDataFromHeader($hdr) {
	$bpc = ord(substr($hdr, 2, 1));
	if (!$bpc) { $bpc = 8; }
	$h = $this->_twobytes2int(substr($hdr, 3, 2));
	$w = $this->_twobytes2int(substr($hdr, 5, 2));
	$channels = ord(substr($hdr, 7, 1));
	if ($channels==3) { $colspace='DeviceRGB'; }
	elseif($channels==4) { $colspace='DeviceCMYK'; }
	else { $colspace='DeviceGray'; }
	return array($w, $h, $colspace, $bpc);
}

function file_get_contents_by_curl($url, &$data) {
	$timeout = 5;
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_NOBODY, 0);
	curl_setopt ( $ch , CURLOPT_RETURNTRANSFER , 1 );
	curl_setopt ( $ch , CURLOPT_CONNECTTIMEOUT , $timeout );
	$data = curl_exec($ch);
	curl_close($ch);
}


function file_get_contents_by_socket($url, &$data) {
	$timeout = 1;
	$p = parse_url($url);
	$file = $p['path'];
	if ($p['query']) { $file .= '?'.$p['query']; }
	if(!($fh = @fsockopen($p['host'], 80, $errno, $errstr, $timeout))) { return false; }
	$getstring =
		"GET ".$file." HTTP/1.0 \r\n" .
		"Host: ".$p['host']." \r\n" .
		"Connection: close\r\n\r\n";
	fwrite($fh, $getstring);
	// Get rid of HTTP header
	$s = fgets($fh, 1024);
	if (!$s) { return false; }
	$httpheader .= $s;
	while (!feof($fh)) {
		$s = fgets($fh, 1024);
		if ( $s == "\r\n" ) { break; }
	}
	$data = '';
	while (!feof($fh)) {
		$data .= fgets($fh, 1024);
	}
	fclose($fh);
}

//==============================================================

function _imageTypeFromString(&$data) {
	$type = '';
	if (substr($data, 6, 4)== 'JFIF' || substr($data, 6, 4)== 'Exif') { 
		$type = 'jpeg'; 
	}
	else if (substr($data, 0, 6)== "GIF87a" || substr($data, 0, 6)== "GIF89a") { 
		$type = 'gif';
	}
	else if (substr($data, 0, 8)== chr(137).'PNG'.chr(13).chr(10).chr(26).chr(10)) { 
		$type = 'png';
	}
/*-- IMAGES-WMF --*/
	else if (substr($data, 0, 4)== chr(215).chr(205).chr(198).chr(154)) { 
		$type = 'wmf';
	}
/*-- END IMAGES-WMF --*/
	else if (preg_match('/<svg.*<\/svg>/is',$data)) { 
		$type = 'svg';
	}
	// BMP images
	else if (substr($data, 0, 2)== "BM") { 
		$type = 'bmp';
	}
	return $type;
}
//==============================================================

// Moved outside WMF as also needed for SVG
function _putformobjects() {
	reset($this->formobjects);
	while(list($file,$info)=each($this->formobjects)) {
		$this->_newobj();
		$this->formobjects[$file]['n']=$this->n;
		$this->_out('<</Type /XObject');
		$this->_out('/Subtype /Form');
		$this->_out('/Group '.($this->n+1).' 0 R');
		$this->_out('/BBox ['.$info['x'].' '.$info['y'].' '.($info['w']+$info['x']).' '.($info['h']+$info['y']).']');
		if ($this->compress)
			$this->_out('/Filter /FlateDecode');
		$data=($this->compress) ? gzcompress($info['data']) : $info['data'];
		$this->_out('/Length '.strlen($data).'>>');
		$this->_putstream($data);
		unset($this->formobjects[$file]['data']);
		$this->_out('endobj');
		// Required for SVG transparency (opacity) to work
		$this->_newobj();
		$this->_out('<</Type /Group');
		$this->_out('/S /Transparency');
		$this->_out('>>');
		$this->_out('endobj');
	}
}

function _freadint($f)
{
	//Read a 4-byte integer from file
	$i=ord(fread($f,1))<<24;
	$i+=ord(fread($f,1))<<16;
	$i+=ord(fread($f,1))<<8;
	$i+=ord(fread($f,1));
	return $i;
}

function _UTF16BEtextstring($s) {
	$s = $this->UTF8ToUTF16BE($s, true);
/*-- ENCRYPTION --*/
	if ($this->encrypted) {
		$s = $this->_RC4($this->_objectkey($this->_current_obj_id), $s);
	}
/*-- END ENCRYPTION --*/
	return '('. $this->_escape($s).')';
}

function _textstring($s) {
/*-- ENCRYPTION --*/
	if ($this->encrypted) {
		$s = $this->_RC4($this->_objectkey($this->_current_obj_id), $s);
	}
/*-- END ENCRYPTION --*/
	return '('. $this->_escape($s).')';
}


function _escape($s)
{
	// the chr(13) substitution fixes the Bugs item #1421290.
	return strtr($s, array(')' => '\\)', '(' => '\\(', '\\' => '\\\\', chr(13) => '\r'));
}

function _putstream($s) {
/*-- ENCRYPTION --*/
	if ($this->encrypted) {
		$s = $this->_RC4($this->_objectkey($this->_current_obj_id), $s);
	}
/*-- END ENCRYPTION --*/
	$this->_out('stream');
	$this->_out($s);
	$this->_out('endstream');
}


function _out($s,$ln=true) {
	if($this->state==2) {
	   if ($this->bufferoutput) {
		$this->headerbuffer.= $s."\n";
	   }
/*-- COLUMNS --*/
	   else if (($this->ColActive) && !$this->processingHeader && !$this->processingFooter) {
		// Captures everything in buffer for columns; Almost everything is sent from fn. Cell() except:
		// Images sent from Image() or
		// later sent as _out($textto) in printbuffer
		// Line()
		if (preg_match('/q \d+\.\d\d+ 0 0 (\d+\.\d\d+) \d+\.\d\d+ \d+\.\d\d+ cm \/(I|FO)\d+ Do Q/',$s,$m)) {	// Image data 
			$h = ($m[1]/_MPDFK);
			// Update/overwrite the lowest bottom of printing y value for a column
			$this->ColDetails[$this->CurrCol]['bottom_margin'] = $this->y+$h;
		}
/*-- TABLES --*/
		else if (preg_match('/\d+\.\d\d+ \d+\.\d\d+ \d+\.\d\d+ ([\-]{0,1}\d+\.\d\d+) re/',$s,$m) && $this->tableLevel>0) { // Rect in table
			$h = ($m[1]/_MPDFK);
			// Update/overwrite the lowest bottom of printing y value for a column
			$this->ColDetails[$this->CurrCol]['bottom_margin'] = max($this->ColDetails[$this->CurrCol]['bottom_margin'],($this->y+$h));
		}
/*-- END TABLES --*/
		else { 	// Td Text Set in Cell()
			if (isset($this->ColDetails[$this->CurrCol]['bottom_margin'])) { $h = $this->ColDetails[$this->CurrCol]['bottom_margin'] - $this->y; }
			else { $h = 0; }
		}
		if ($h < 0) { $h = -$h; }
		$this->columnbuffer[] = array(
		's' => $s,							// Text string to output 
		'col' => $this->CurrCol, 				// Column when printed 
		'x' => $this->x, 						// x when printed 
		'y' => $this->y,					 	// this->y when printed (after column break) 
		'h' => $h						 	// actual y at bottom when printed = y+h  
		);
	   }
/*-- END COLUMNS --*/
/*-- TABLES --*/
	   else if ($this->table_rotate && !$this->processingHeader && !$this->processingFooter) {
		// Captures eveything in buffer for rotated tables; 
		$this->tablebuffer .= $s . "\n";
	   }
/*-- END TABLES --*/
	   else if ($this->kwt && !$this->processingHeader && !$this->processingFooter) {
		// Captures eveything in buffer for keep-with-table (h1-6); 
		$this->kwt_buffer[] = array(
		's' => $s,							// Text string to output 
		'x' => $this->x, 						// x when printed  
		'y' => $this->y,					 	// y when printed  
		);
	   }
	   else if (($this->keep_block_together) && !$this->processingHeader && !$this->processingFooter) {
		if (!isset($this->ktBlock[$this->page]['bottom_margin'])) {
			$this->ktBlock[$this->page]['bottom_margin'] = $this->y;
		}

		// Captures eveything in buffer; 
		if (preg_match('/q \d+\.\d\d+ 0 0 (\d+\.\d\d+) \d+\.\d\d+ \d+\.\d\d+ cm \/(I|FO)\d+ Do Q/',$s,$m)) {	// Image data 
			$h = ($m[1]/_MPDFK);
			// Update/overwrite the lowest bottom of printing y value for Keep together block
			$this->ktBlock[$this->page]['bottom_margin'] = $this->y+$h;
		}
		else { 	// Td Text Set in Cell()
			if (isset($this->ktBlock[$this->page]['bottom_margin'])) { $h = $this->ktBlock[$this->page]['bottom_margin'] - $this->y; }
			else { $h = 0; }
		}
		if ($h < 0) { $h = -$h; }
		$this->divbuffer[] = array(
		'page' => $this->page,
		's' => $s,							// Text string to output 
		'x' => $this->x, 						// x when printed 
		'y' => $this->y,					 	// y when printed (after column break)
		'h' => $h						 	// actual y at bottom when printed = y+h
		);
	   }
	   else {
		$this->pages[$this->page] .= $s.($ln == true ? "\n" : '');
	   }

	}
	else {
		$this->buffer .= $s.($ln == true ? "\n" : '');
	}
}

/*-- WATERMARK --*/
// add a watermark 
function watermark( $texte, $angle=45, $fontsize=96, $alpha=0.2 ) {
	if ($this->PDFA || $this->PDFX) { $this->Error('PDFA and PDFX do not permit transparency, so mPDF does not allow Watermarks!'); }
	if (!$this->watermark_font) { $this->watermark_font = $this->default_font; }
      $this->SetFont( $this->watermark_font, "B", $fontsize, false );	// Don't output
	$texte= $this->purify_utf8_text($texte);
	if ($this->text_input_as_HTML) {
		$texte= $this->all_entities_to_utf8($texte);
	}
	if ($this->usingCoreFont) { $texte = mb_convert_encoding($texte,$this->mb_enc,'UTF-8'); }
	// DIRECTIONALITY
	$this->magic_reverse_dir($texte, true, $this->directionality);	// *RTL*
	// Font-specific ligature substitution for Indic fonts
	if (isset($this->CurrentFont['indic']) && $this->CurrentFont['indic']) $this->ConvertIndic($texte);	// *INDIC*

	$this->SetAlpha($alpha);

	$this->SetTColor($this->ConvertColor(0));
	$szfont = $fontsize;
	$loop   = 0;
	$maxlen = (min($this->w,$this->h) );	// sets max length of text as 7/8 width/height of page
	while ( $loop == 0 )
	{
       $this->SetFont( $this->watermark_font, "B", $szfont, false );	// Don't output
	 $offset =  ((sin(deg2rad($angle))) * ($szfont/_MPDFK));

       $strlen = $this->GetStringWidth($texte);
       if ( $strlen > $maxlen - $offset  )
          $szfont --;
       else
          $loop ++;
	}

	$this->SetFont( $this->watermark_font, "B", $szfont-0.1, true, true);	// Output The -0.1 is because SetFont above is not written to PDF
											// Repeating it will not output anything as mPDF thinks it is set
	$adj = ((cos(deg2rad($angle))) * ($strlen/2));
	$opp = ((sin(deg2rad($angle))) * ($strlen/2));
	$wx = ($this->w/2) - $adj + $offset/3;
	$wy = ($this->h/2) + $opp;
	$this->Rotate($angle,$wx,$wy);
	$this->Text($wx,$wy,$texte);
	$this->Rotate(0);
	$this->SetTColor($this->ConvertColor(0));

	$this->SetAlpha(1);

}

function watermarkImg( $src, $alpha=0.2 ) {
	if ($this->PDFA || $this->PDFX) { $this->Error('PDFA and PDFX do not permit transparency, so mPDF does not allow Watermarks!'); }
	if ($this->watermarkImgBehind) { $this->watermarkImgAlpha = $this->SetAlpha($alpha, 'Normal', true); }
	else { $this->SetAlpha($alpha, $this->watermarkImgAlphaBlend); }
	$this->Image($src,0,0,0,0,'','', true, true, true);
	if (!$this->watermarkImgBehind) { $this->SetAlpha(1); }
}
/*-- END WATERMARK --*/


function Rotate($angle,$x=-1,$y=-1)
{
	if($x==-1)
		$x=$this->x;
	if($y==-1)
		$y=$this->y;
	if($this->angle!=0)
		$this->_out('Q');
	$this->angle=$angle;
	if($angle!=0)
	{
		$angle*=M_PI/180;
		$c=cos($angle);
		$s=sin($angle);
		$cx=$x*_MPDFK;
		$cy=($this->h-$y)*_MPDFK;
		$this->_out(sprintf('q %.5F %.5F %.5F %.5F %.3F %.3F cm 1 0 0 1 %.3F %.3F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
	}
}



function CircularText($x, $y, $r, $text, $align='top', $fontfamily='', $fontsize=0, $fontstyle='', $kerning=120, $fontwidth=100, $divider) {	// mPDF 5.5.23
	if (!class_exists('directw', false)) { include(_MPDF_PATH.'classes/directw.php'); }
	if (empty($this->directw)) { $this->directw = new directw($this); }
	$this->directw->CircularText($x, $y, $r, $text, $align, $fontfamily, $fontsize, $fontstyle, $kerning, $fontwidth, $divider);	// mPDF 5.5.23
}


// From Invoice
function RoundedRect($x, $y, $w, $h, $r, $style = '')
{
	$hp = $this->h;
	if($style=='F')
		$op='f';
	elseif($style=='FD' or $style=='DF')
		$op='B';
	else
		$op='S';
	$MyArc = 4/3 * (sqrt(2) - 1);
	$this->_out(sprintf('%.3F %.3F m',($x+$r)*_MPDFK,($hp-$y)*_MPDFK ));
	$xc = $x+$w-$r ;
	$yc = $y+$r;
	$this->_out(sprintf('%.3F %.3F l', $xc*_MPDFK,($hp-$y)*_MPDFK ));

	$this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
	$xc = $x+$w-$r ;
	$yc = $y+$h-$r;
	$this->_out(sprintf('%.3F %.3F l',($x+$w)*_MPDFK,($hp-$yc)*_MPDFK));
	$this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
	$xc = $x+$r ;
	$yc = $y+$h-$r;
	$this->_out(sprintf('%.3F %.3F l',$xc*_MPDFK,($hp-($y+$h))*_MPDFK));
	$this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
	$xc = $x+$r ;
	$yc = $y+$r;
	$this->_out(sprintf('%.3F %.3F l',($x)*_MPDFK,($hp-$yc)*_MPDFK ));
	$this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
	$this->_out($op);
}

function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
{
	$h = $this->h;
	$this->_out(sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', $x1*_MPDFK, ($h-$y1)*_MPDFK,
						$x2*_MPDFK, ($h-$y2)*_MPDFK, $x3*_MPDFK, ($h-$y3)*_MPDFK));
}




//====================================================



/*-- DIRECTW --*/
function Shaded_box( $text,$font='',$fontstyle='B',$szfont='',$width='70%',$style='DF',$radius=2.5,$fill='#FFFFFF',$color='#000000',$pad=2 ) {
	// F (shading - no line),S (line, no shading),DF (both)
	if (!class_exists('directw', false)) { include(_MPDF_PATH.'classes/directw.php'); }
	if (empty($this->directw)) { $this->directw = new directw($this); }
	$this->directw->Shaded_box( $text,$font,$fontstyle,$szfont,$width,$style,$radius,$fill,$color,$pad);
}
/*-- END DIRECTW --*/


function UTF8StringToArray($str, $addSubset=true) {
   $out = array();
   $len = strlen($str);
   for ($i = 0; $i < $len; $i++) {
	$uni = -1;
      $h = ord($str[$i]);
      if ( $h <= 0x7F )
         $uni = $h;
      elseif ( $h >= 0xC2 ) {
         if ( ($h <= 0xDF) && ($i < $len -1) )
            $uni = ($h & 0x1F) << 6 | (ord($str[++$i]) & 0x3F);
         elseif ( ($h <= 0xEF) && ($i < $len -2) )
            $uni = ($h & 0x0F) << 12 | (ord($str[++$i]) & 0x3F) << 6 | (ord($str[++$i]) & 0x3F);
         elseif ( ($h <= 0xF4) && ($i < $len -3) )
            $uni = ($h & 0x0F) << 18 | (ord($str[++$i]) & 0x3F) << 12 | (ord($str[++$i]) & 0x3F) << 6 | (ord($str[++$i]) & 0x3F);
      }
	if ($uni >= 0) {
		$out[] = $uni;
		if ($addSubset && isset($this->CurrentFont['subset'])) {
			$this->CurrentFont['subset'][$uni] = $uni;
		}
	}
   }
   return $out;
}


//Convert utf-8 string to <HHHHHH> for Font Subsets
function UTF8toSubset($str) {
	$ret = '<';
	$str = preg_replace('/'.preg_quote($this->aliasNbPg,'/').'/', chr(7), $str );
	$str = preg_replace('/'.preg_quote($this->aliasNbPgGp,'/').'/', chr(8), $str );
	$unicode = $this->UTF8StringToArray($str);
	$orig_fid = $this->CurrentFont['subsetfontids'][0];
	$last_fid = $this->CurrentFont['subsetfontids'][0];
	foreach($unicode as $c) {
	   if ($c == 7 || $c == 8) { 
			if ($orig_fid != $last_fid) {
				$ret .= '> Tj /F'.$orig_fid.' '.$this->FontSizePt.' Tf <';
				$last_fid = $orig_fid;
			}
			if ($c == 7) { $ret .= $this->aliasNbPgHex; }
			else { $ret .= $this->aliasNbPgGpHex; }
			continue;
	   }
	   for ($i=0; $i<99; $i++) {
		// return c as decimal char
		$init = array_search($c, $this->CurrentFont['subsets'][$i]);
		if ($init!==false) {
			if ($this->CurrentFont['subsetfontids'][$i] != $last_fid) {
				$ret .= '> Tj /F'.$this->CurrentFont['subsetfontids'][$i].' '.$this->FontSizePt.' Tf <';
				$last_fid = $this->CurrentFont['subsetfontids'][$i];
			}
			$ret .= sprintf("%02s", strtoupper(dechex($init)));
			break;
		}
		// TrueType embedded SUBSETS
		else if (count($this->CurrentFont['subsets'][$i]) < 255) {
			$n = count($this->CurrentFont['subsets'][$i]);
			$this->CurrentFont['subsets'][$i][$n] = $c;
			if ($this->CurrentFont['subsetfontids'][$i] != $last_fid) {
				$ret .= '> Tj /F'.$this->CurrentFont['subsetfontids'][$i].' '.$this->FontSizePt.' Tf <';
				$last_fid = $this->CurrentFont['subsetfontids'][$i];
			}
			$ret .= sprintf("%02s", strtoupper(dechex($n)));
			break;
		}
		else if (!isset($this->CurrentFont['subsets'][($i+1)])) {
			// TrueType embedded SUBSETS
			$this->CurrentFont['subsets'][($i+1)] = array(0=>0);
			$new_fid = count($this->fonts)+$this->extraFontSubsets+1;
			$this->CurrentFont['subsetfontids'][($i+1)] = $new_fid;
			$this->extraFontSubsets++;
		}
	   }
	}
	$ret .= '>';
	if ($last_fid != $orig_fid) {
		$ret .= ' Tj /F'.$orig_fid.' '.$this->FontSizePt.' Tf <> ';
	}
	return $ret;
}


// Converts UTF-8 strings to UTF16-BE.
function UTF8ToUTF16BE($str, $setbom=true) {
	if ($this->checkSIP && preg_match("/([\x{20000}-\x{2FFFF}])/u", $str)) { 
	   if (!in_array($this->currentfontfamily, array('gb','big5','sjis','uhc','gbB','big5B','sjisB','uhcB','gbI','big5I','sjisI','uhcI',
		'gbBI','big5BI','sjisBI','uhcBI'))) {
		$str = preg_replace("/[\x{20000}-\x{2FFFF}]/u", chr(0), $str);
	   }
	}
	if ($this->checkSMP && preg_match("/([\x{10000}-\x{1FFFF}])/u", $str )) { 
		$str = preg_replace("/[\x{10000}-\x{1FFFF}]/u", chr(0), $str );
	}
	$outstr = ""; // string to be returned
	if ($setbom) {
		$outstr .= "\xFE\xFF"; // Byte Order Mark (BOM)
	}
	$outstr .= mb_convert_encoding($str, 'UTF-16BE', 'UTF-8');
	return $outstr;
}





// ====================================================
// ====================================================
/*-- CJK-FONTS --*/

// from class PDF_Chinese CJK EXTENSIONS
function AddCIDFont($family,$style,$name,&$cw,$CMap,$registry,$desc)
{
	$fontkey=strtolower($family).strtoupper($style);
	if(isset($this->fonts[$fontkey]))
		$this->Error("Font already added: $family $style");
	$i=count($this->fonts)+$this->extraFontSubsets+1;
	$name=str_replace(' ','',$name);
	if ($family == 'sjis') { $up = -120; } else { $up = -130; }
	// ? 'up' and 'ut' do not seem to be referenced anywhere
	$this->fonts[$fontkey]=array('i'=>$i,'type'=>'Type0','name'=>$name,'up'=>$up,'ut'=>40,'cw'=>$cw,'CMap'=>$CMap,'registry'=>$registry,'MissingWidth'=>1000,'desc'=>$desc);
}

function AddCJKFont($family) {

	if ($this->PDFA || $this->PDFX) {
		$this->Error("Adobe CJK fonts cannot be embedded in mPDF (required for PDFA1-b and PDFX/1-a).");
	}
	if ($family == 'big5') { $this->AddBig5Font(); }
	else if ($family == 'gb') { $this->AddGBFont(); }
	else if ($family == 'sjis') { $this->AddSJISFont(); }
	else if ($family == 'uhc') { $this->AddUHCFont(); }
}

function AddBig5Font()
{
	//Add Big5 font with proportional Latin
	$family='big5';
	$name='MSungStd-Light-Acro';
	$cw=$this->Big5_widths;
	$CMap='UniCNS-UTF16-H';
	$registry=array('ordering'=>'CNS1','supplement'=>4);
	$desc = array(
	'Ascent' => 880,
	'Descent' => -120,
	'CapHeight' => 880,
	'Flags' => 6,
	'FontBBox' => '[-160 -249 1015 1071]',
	'ItalicAngle' => 0,
	'StemV' => 93,
	);
	$this->AddCIDFont($family,'',$name,$cw,$CMap,$registry,$desc);
	$this->AddCIDFont($family,'B',$name.',Bold',$cw,$CMap,$registry,$desc);
	$this->AddCIDFont($family,'I',$name.',Italic',$cw,$CMap,$registry,$desc);
	$this->AddCIDFont($family,'BI',$name.',BoldItalic',$cw,$CMap,$registry,$desc);
}


function AddGBFont()
{
	//Add GB font with proportional Latin
	$family='gb';
	$name='STSongStd-Light-Acro';	
	$cw=$this->GB_widths;
	$CMap='UniGB-UTF16-H';
	$registry=array('ordering'=>'GB1','supplement'=>4);
	$desc = array(
	'Ascent' => 752,
	'Descent' => -271,
	'CapHeight' => 737,
	'Flags' => 6,
	'FontBBox' => '[-25 -254 1000 880]',
	'ItalicAngle' => 0,
	'StemV' => 58,
	'Style' => '<< /Panose <000000000400000000000000> >>',
	);
	$this->AddCIDFont($family,'',$name,$cw,$CMap,$registry,$desc);
	$this->AddCIDFont($family,'B',$name.',Bold',$cw,$CMap,$registry,$desc);
	$this->AddCIDFont($family,'I',$name.',Italic',$cw,$CMap,$registry,$desc);
	$this->AddCIDFont($family,'BI',$name.',BoldItalic',$cw,$CMap,$registry,$desc);
}


function AddSJISFont()
{
	//Add SJIS font with proportional Latin
	$family='sjis';
	$name='KozMinPro-Regular-Acro';
	$cw=$this->SJIS_widths;
	$CMap='UniJIS-UTF16-H';
	$registry=array('ordering'=>'Japan1','supplement'=>5);
	$desc = array(
	'Ascent' => 880,
	'Descent' => -120,
	'CapHeight' => 740,
	'Flags' => 6,
	'FontBBox' => '[-195 -272 1110 1075]',
	'ItalicAngle' => 0,
	'StemV' => 86,
	'XHeight' => 502,
	);
	$this->AddCIDFont($family,'',$name,$cw,$CMap,$registry,$desc);
	$this->AddCIDFont($family,'B',$name.',Bold',$cw,$CMap,$registry,$desc);
	$this->AddCIDFont($family,'I',$name.',Italic',$cw,$CMap,$registry,$desc);
	$this->AddCIDFont($family,'BI',$name.',BoldItalic',$cw,$CMap,$registry,$desc);
}

function AddUHCFont()
{
	//Add UHC font with proportional Latin
	$family='uhc';
	$name='HYSMyeongJoStd-Medium-Acro';	
	$cw=$this->UHC_widths;
	$CMap='UniKS-UTF16-H';
	$registry=array('ordering'=>'Korea1','supplement'=>2);
	$desc = array(
	'Ascent' => 880,
	'Descent' => -120,
	'CapHeight' => 720,
	'Flags' => 6,
	'FontBBox' => '[-28 -148 1001 880]',
	'ItalicAngle' => 0,
	'StemV' => 60,
	'Style' => '<< /Panose <000000000600000000000000> >>',
	);
	$this->AddCIDFont($family,'',$name,$cw,$CMap,$registry,$desc);
	$this->AddCIDFont($family,'B',$name.',Bold',$cw,$CMap,$registry,$desc);
	$this->AddCIDFont($family,'I',$name.',Italic',$cw,$CMap,$registry,$desc);
	$this->AddCIDFont($family,'BI',$name.',BoldItalic',$cw,$CMap,$registry,$desc);
}

/*-- END CJK-FONTS --*/

//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
function SetAutoFont($af = AUTOFONT_ALL) {
	if ($this->onlyCoreFonts) { return false; }
	if (!$af && $af !== 0) { $af = AUTOFONT_ALL; }
	$this->autoFontGroups = $af;
	if ($this->autoFontGroups ) { 
		$this->useLang = true;
	}
}


function SetDefaultFont($font) {
	// Disallow embedded fonts to be used as defaults in PDFA
	if ($this->PDFA || $this->PDFX) {
		if (strtolower($font) == 'ctimes') { $font = 'serif'; }
		if (strtolower($font) == 'ccourier') { $font = 'monospace'; }
		if (strtolower($font) == 'chelvetica') { $font = 'sans-serif'; }
	}
  	$font = $this->SetFont($font);	// returns substituted font if necessary
	$this->default_font = $font;
	$this->original_default_font = $font;
	if (!$this->watermark_font ) { $this->watermark_font = $font; }	// *WATERMARK*
	$this->defaultCSS['BODY']['FONT-FAMILY'] = $font;
	$this->cssmgr->CSS['BODY']['FONT-FAMILY'] = $font;
}

function SetDefaultFontSize($fontsize) {
	$this->default_font_size = $fontsize;
	$this->original_default_font_size = $fontsize;
	$this->SetFontSize($fontsize);
	$this->defaultCSS['BODY']['FONT-SIZE'] = $fontsize . 'pt';
	$this->cssmgr->CSS['BODY']['FONT-SIZE'] = $fontsize . 'pt';
}

function SetDefaultBodyCSS($prop, $val) {
   if ($prop) {
	$this->defaultCSS['BODY'][strtoupper($prop)] = $val;
	$this->cssmgr->CSS['BODY'][strtoupper($prop)] = $val;
  }
}


function SetDirectionality($dir='ltr') {
/*-- RTL --*/
	if (strtolower($dir) == 'rtl') { 
	  if ($this->directionality != 'rtl') {
		// Swop L/R Margins so page 1 RTL is an 'even' page
		$tmp = $this->DeflMargin;
		$this->DeflMargin = $this->DefrMargin; 
		$this->DefrMargin = $tmp; 
		$this->orig_lMargin = $this->DeflMargin;
		$this->orig_rMargin = $this->DefrMargin;

		$this->SetMargins($this->DeflMargin,$this->DefrMargin,$this->tMargin);
	  }
		$this->directionality = 'rtl'; 
		$this->defaultAlign = 'R';
		$this->defaultTableAlign = 'R';
	}
	else  { 
/*-- END RTL --*/
		$this->directionality = 'ltr'; 
		$this->defaultAlign = 'L';
		$this->defaultTableAlign = 'L';
	}	// *RTL*
	$this->cssmgr->CSS['BODY']['DIRECTION'] = $this->directionality;
}



// Added to set line-height-correction
function SetLineHeightCorrection($val) {
	if ($val > 0) { $this->default_lineheight_correction = $val; }
	else { $this->default_lineheight_correction = 1.2; }
}

// Set a (fixed) lineheight to an actual value - either to named fontsize(pts) or default
function SetLineHeight($FontPt='',$spacing = '') {
   if ($this->shrin_k > 1) { $k = $this->shrin_k; }
   else { $k = 1; }
   if ($spacing > 0) { 
	if (preg_match('/mm/',$spacing)) { 
		$this->lineheight = ($spacing + 0.0) / $k; // convert to number
	}
	else  { 
		if ($FontPt) { $this->lineheight = (($FontPt/_MPDFK) *$spacing); }
		else { $this->lineheight = (($this->FontSizePt/_MPDFK) *$spacing); }
	}
   }
   else {
	if ($FontPt) { $this->lineheight = (($FontPt/_MPDFK) *$this->normalLineheight); }
	else { $this->lineheight = (($this->FontSizePt/_MPDFK) *$this->normalLineheight); }
   }
}

function _computeLineheight($lh, $fs='') {
	if ($this->shrin_k > 1) { $k = $this->shrin_k; }
	else { $k = 1; }
	if (!$fs) { $fs = $this->FontSize; }
	if (preg_match('/mm/',$lh)) { 
		return (($lh + 0.0) / $k); // convert to number
	}
	else if ($lh > 0) { 
		return ($fs * $lh);
	}
	else if (isset($this->normalLineheight)) { return ($fs * $this->normalLineheight); }
	else return ($fs * $this->default_lineheight_correction); 
}


function SetBasePath($str='') {
  if ( isset($_SERVER['HTTP_HOST']) ) { $host = $_SERVER['HTTP_HOST']; }
  else if ( isset($_SERVER['SERVER_NAME']) ) { $host = $_SERVER['SERVER_NAME']; }
  else { $host = ''; }
  if (!$str) { 
	if ($_SERVER['SCRIPT_NAME']) { $currentPath = dirname($_SERVER['SCRIPT_NAME']); }
	else { $currentPath = dirname($_SERVER['PHP_SELF']); }
	$currentPath = str_replace("\\","/",$currentPath);
	if ($currentPath == '/') { $currentPath = ''; }
	if ($host) { $currpath = 'http://' . $host . $currentPath .'/'; }
	else { $currpath = ''; }
	$this->basepath = $currpath; 
	$this->basepathIsLocal = true; 
	return; 
  }
  $str = preg_replace('/\?.*/','',$str);
  if (!preg_match('/(http|https|ftp):\/\/.*\//i',$str)) { $str .= '/'; } 
  $str .= 'xxx';	// in case $str ends in / e.g. http://www.bbc.co.uk/
  $this->basepath = dirname($str) . "/";	// returns e.g. e.g. http://www.google.com/dir1/dir2/dir3/
  $this->basepath = str_replace("\\","/",$this->basepath); //If on Windows
  $tr = parse_url($this->basepath);
  if (isset($tr['host']) && ($tr['host'] == $host)) { $this->basepathIsLocal = true; }
  else { $this->basepathIsLocal = false; }
}


function GetFullPath(&$path,$basepath='') {
	// When parsing CSS need to pass temporary basepath - so links are relative to current stylesheet
	if (!$basepath) { $basepath = $this->basepath; }
	//Fix path value
	$path = str_replace("\\","/",$path); //If on Windows
	$path = preg_replace('/^\/\//','http://',$path);	// mPDF 5.6.27
	$regexp = '|^./|';	// Inadvertently corrects "./path/etc" and "//www.domain.com/etc"
	$path = preg_replace($regexp,'',$path);


	if(substr($path,0,1) == '#') { return; }
	if (stristr($path,"mailto:") !== false) { return; }
	if (strpos($path,"../") !== false ) { //It is a Relative Link
		$backtrackamount = substr_count($path,"../");
		$maxbacktrack = substr_count($basepath,"/") - 3;	// mPDF 5.6.18
		$filepath = str_replace("../",'',$path);
		$path = $basepath;
		//If it is an invalid relative link, then make it go to directory root
		if ($backtrackamount > $maxbacktrack) $backtrackamount = $maxbacktrack;
		//Backtrack some directories
		for( $i = 0 ; $i < $backtrackamount + 1 ; $i++ ) $path = substr( $path, 0 , strrpos($path,"/") );
		$path = $path . "/" . $filepath; //Make it an absolute path
	}
	else if( strpos($path,":/") === false || strpos($path,":/") > 10) { //It is a Local Link
		if (substr($path,0,1) == "/") { 
			$tr = parse_url($basepath);
			$root = $tr['scheme'].'://'.$tr['host'];
			$path = $root . $path; 
		}
		else { $path = $basepath . $path; }
	}
	//Do nothing if it is an Absolute Link
}


// Used for external CSS files
function _get_file($path) {
	// If local file try using local path (? quicker, but also allowed even if allow_url_fopen false)
	$contents = '';
	$contents = @file_get_contents($path);
	if ($contents) { return $contents; }
	if ($this->basepathIsLocal) {
		$tr = parse_url($path);
		$lp=getenv("SCRIPT_NAME");
		$ap=realpath($lp);
		$ap=str_replace("\\","/",$ap);
		$docroot=substr($ap,0,strpos($ap,$lp));
		// WriteHTML parses all paths to full URLs; may be local file name 
		if ($tr['scheme'] && $tr['host'] && $_SERVER["DOCUMENT_ROOT"] ) { 
			$localpath = $_SERVER["DOCUMENT_ROOT"] . $tr['path']; 
		}
		// DOCUMENT_ROOT is not returned on IIS
		else if ($docroot) {
			$localpath = $docroot . $tr['path'];
		}
		else { $localpath = $path; }
		$contents = @file_get_contents($localpath);
	}
	// if not use full URL
	else if (!$contents && !ini_get('allow_url_fopen') && function_exists("curl_init"))  {
		$ch = curl_init($path);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt ( $ch , CURLOPT_RETURNTRANSFER , 1 );
		$contents = curl_exec($ch);
		curl_close($ch);
	}
	return $contents;
}


function docPageNum($num = 0, $extras = false) {
	if ($num < 1) { $num = $this->page; }
	$type = '1';	// set default decimal
	$ppgno = $num;
	$suppress = 0;
	$offset = 0;
	$lastreset = 0;
	foreach($this->PageNumSubstitutions AS $psarr) {
		if ($num >= $psarr['from']) {
			if ($psarr['reset']) { 
				if ($psarr['reset']>1) { $offset = $psarr['reset']-1; }
				$ppgno = $num - $psarr['from'] + 1 + $offset; 
				$lastreset = $psarr['from'];
			}
			if ($psarr['type']) { $type = $psarr['type']; }
			if (strtoupper($psarr['suppress'])=='ON' || $psarr['suppress']==1) { $suppress = 1; }
			else if (strtoupper($psarr['suppress'])=='OFF') { $suppress = 0; }
		}
	}
	if ($suppress) { return ''; }

	foreach($this->pgsIns AS $k=>$v) {
		if ($k>$lastreset && $k<$num) {
			$ppgno -= $v;
		}
	}
	if ($type=='A') { $ppgno = $this->dec2alpha($ppgno,true); }
	else if ($type=='a') { $ppgno = $this->dec2alpha($ppgno,false);}
	else if ($type=='I') { $ppgno = $this->dec2roman($ppgno,true); }
	else if ($type=='i') { $ppgno = $this->dec2roman($ppgno,false); }
	if ($extras) { $ppgno = $this->pagenumPrefix . $ppgno . $this->pagenumSuffix; }
	return $ppgno;
}


function docPageSettings($num = 0) {
	// Returns current type (numberstyle), suppression state for this page number; 
	// reset is only returned if set for this page number
	if ($num < 1) { $num = $this->page; }
	$type = '1';	// set default decimal
	$ppgno = $num;
	$suppress = 0;
	$offset = 0;
	$reset = '';
	foreach($this->PageNumSubstitutions AS $psarr) {
		if ($num >= $psarr['from']) {
			if ($psarr['reset']) { 
				if ($psarr['reset']>1) { $offset = $psarr['reset']-1; }
				$ppgno = $num - $psarr['from'] + 1 + $offset; 
			}
			if ($psarr['type']) { $type = $psarr['type']; }
			if (strtoupper($psarr['suppress'])=='ON' || $psarr['suppress']==1) { $suppress = 1; }
			else if (strtoupper($psarr['suppress'])=='OFF') { $suppress = 0; }
		}
		if ($num == $psarr['from']) { $reset = $psarr['reset']; }
	}
	if ($suppress) { $suppress = 'on'; }
	else { $suppress = 'off'; }
	return array($type, $suppress, $reset);
}

function docPageNumTotal($num = 0, $extras = false) {
	if ($num < 1) { $num = $this->page; }
	$type = '1';	// set default decimal
	$ppgstart = 1;
	$ppgend = count($this->pages)+1; 
	$suppress = 0;
	$offset = 0;
	foreach($this->PageNumSubstitutions AS $psarr) {
		if ($num >= $psarr['from']) {
			if ($psarr['reset']) { 
				if ($psarr['reset']>1) { $offset = $psarr['reset']-1; }
				$ppgstart = $psarr['from'] + $offset; 
				$ppgend = count($this->pages)+1 + $offset; 
			}
			if ($psarr['type']) { $type = $psarr['type']; }
			if (strtoupper($psarr['suppress'])=='ON' || $psarr['suppress']==1) { $suppress = 1; }
			else if (strtoupper($psarr['suppress'])=='OFF') { $suppress = 0; }
		}
		if ($num < $psarr['from']) {
			if ($psarr['reset']) { 
				$ppgend = $psarr['from'] + $offset; 
				break;
			}
		}
	}
	if ($suppress) { return ''; }
	$ppgno = $ppgend-$ppgstart+$offset; 

	// mPDF 5.6.47
	foreach($this->pgsIns AS $k => $v) {
		if ($k>$ppgstart && $k<$ppgend) {
			$ppgno -= $v;
		}
	}

	if ($extras) { $ppgno = $this->nbpgPrefix . $ppgno . $this->nbpgSuffix; }
	return $ppgno;
}

function RestartDocTemplate() {
	$this->docTemplateStart = $this->page;
}



//Page header
function Header($content='') {

	$this->cMarginL = 0;
	$this->cMarginR = 0;


/*-- HTMLHEADERS-FOOTERS --*/
  if (($this->mirrorMargins && ($this->page%2==0) && $this->HTMLHeaderE) || ($this->mirrorMargins && ($this->page%2==1) && $this->HTMLHeader) || (!$this->mirrorMargins && $this->HTMLHeader)) {
	$this->writeHTMLHeaders(); 
	return;
  }
/*-- END HTMLHEADERS-FOOTERS --*/
  $this->processingHeader=true;
  $h = $this->headerDetails;
  if(count($h)) {

	if ($this->forcePortraitHeaders && $this->CurOrientation=='L' && $this->CurOrientation!=$this->DefOrientation) {
		$this->_out(sprintf('q 0 -1 1 0 0 %.3F cm ',($this->h*_MPDFK)));
		$yadj = $this->w - $this->h;
		$headerpgwidth = $this->h - $this->orig_lMargin - $this->orig_rMargin;
		if (($this->mirrorMargins) && (($this->page)%2==0)) {	// EVEN
			$headerlmargin = $this->orig_rMargin;
		}
		else {
			$headerlmargin = $this->orig_lMargin;
		}
	}
	else { 
		$yadj = 0; 
		$headerpgwidth = $this->pgwidth;
		$headerlmargin = $this->lMargin;
	}

	$this->y = $this->margin_header - $yadj ;
	$this->SetTColor($this->ConvertColor(0));
    	$this->SUP = false;
	$this->SUB = false;
	$this->bullet = false;

	// only show pagenumber if numbering on
	$pgno = $this->docPageNum($this->page, true); 

	if (($this->mirrorMargins) && (($this->page)%2==0)) {	// EVEN
			$side = 'even';
	}
	else {	// ODD	// OR NOT MIRRORING MARGINS/FOOTERS = DEFAULT
			$side = 'odd';
	}
	$maxfontheight = 0;
	foreach(array('L','C','R') AS $pos) {
	  if (isset($h[$side][$pos]['content']) && $h[$side][$pos]['content']) {
		if (isset($h[$side][$pos]['font-size']) && $h[$side][$pos]['font-size']) { $hfsz = $h[$side][$pos]['font-size']; }
		else { $hfsz = $this->default_font_size; }
		$maxfontheight = max($maxfontheight,$hfsz);
	  }
	}
	// LEFT-CENTER-RIGHT
	foreach(array('L','C','R') AS $pos) {
	  if (isset($h[$side][$pos]['content']) && $h[$side][$pos]['content']) {
		$hd = str_replace('{PAGENO}',$pgno,$h[$side][$pos]['content']);
		$hd = str_replace($this->aliasNbPgGp,$this->nbpgPrefix.$this->aliasNbPgGp.$this->nbpgSuffix,$hd);
		$hd = preg_replace('/\{DATE\s+(.*?)\}/e',"date('\\1')",$hd);
		if (isset($h[$side][$pos]['font-family']) && $h[$side][$pos]['font-family']) { $hff = $h[$side][$pos]['font-family']; }
		else { $hff = $this->original_default_font; }
		if (isset($h[$side][$pos]['font-size']) && $h[$side][$pos]['font-size']) { $hfsz = $h[$side][$pos]['font-size']; }
		else { $hfsz = $this->original_default_font_size; }	// pts
		$maxfontheight = max($maxfontheight,$hfsz);
		$hfst = '';
		if (isset($h[$side][$pos]['font-style']) && $h[$side][$pos]['font-style']) { 
			$hfst = $h[$side][$pos]['font-style'];
		}
		if (isset($h[$side][$pos]['color']) && $h[$side][$pos]['color']) { 
			$hfcol = $h[$side][$pos]['color']; 
			$cor = $this->ConvertColor($hfcol);
			if ($cor) { $this->SetTColor($cor); }
		}
		else { $hfcol = ''; }
		$this->SetFont($hff,$hfst,$hfsz,true,true);
		$this->x = $headerlmargin ;
		$this->y = $this->margin_header - $yadj ;

		$hd = $this->purify_utf8_text($hd);
		if ($this->text_input_as_HTML) {
			$hd = $this->all_entities_to_utf8($hd);
		}
		// CONVERT CODEPAGE
		if ($this->usingCoreFont) { $hd = mb_convert_encoding($hd,$this->mb_enc,'UTF-8'); }
		// DIRECTIONALITY RTL
		$this->magic_reverse_dir($hd, true, $this->directionality);	// *RTL*
		// Font-specific ligature substitution for Indic fonts
		if (isset($this->CurrentFont['indic']) && $this->CurrentFont['indic']) $this->ConvertIndic($hd);	// *INDIC*
		$align = $pos;
/*-- RTL --*/
		if ($this->directionality == 'rtl') { 
			if ($pos == 'L') { $align = 'R'; }
			else if ($pos == 'R') { $align = 'L'; }
		}
/*-- END RTL --*/
		if ($pos!='L' && (strpos($hd,$this->aliasNbPg)!==false || strpos($hd,$this->aliasNbPgGp)!==false)) { 
			if (strpos($hd,$this->aliasNbPgGp)!==false) { $type= 'nbpggp'; } else { $type= 'nbpg'; }
			$this->_out('{mpdfheader'.$type.' '.$pos.' ff='.$hff.' fs='.$hfst.' fz='.$hfsz.'}'); 
			$this->Cell($headerpgwidth ,$maxfontheight/_MPDFK ,$hd,0,0,$align,0,'',0,0,0,'M');
			$this->_out('Q');
		}
		else { 
			$this->Cell($headerpgwidth ,$maxfontheight/_MPDFK ,$hd,0,0,$align,0,'',0,0,0,'M');
		}
		if ($hfcol) { $this->SetTColor($this->ConvertColor(0)); }
	  }
	}
	//Return Font to normal
	$this->SetFont($this->default_font,'',$this->original_default_font_size);
	// LINE
	if (isset($h[$side]['line']) && $h[$side]['line']) { 
		$this->SetLineWidth(0.1);
		$this->SetDColor($this->ConvertColor(0));
		$this->Line($headerlmargin , $this->margin_header + ($maxfontheight*(1+$this->header_line_spacing)/_MPDFK) - $yadj , $headerlmargin + $headerpgwidth, $this->margin_header + ($maxfontheight*(1+$this->header_line_spacing)/_MPDFK) - $yadj  );
	}
	if ($this->forcePortraitHeaders && $this->CurOrientation=='L' && $this->CurOrientation!=$this->DefOrientation) {
		$this->_out('Q');
	}
  }
  $this->SetY($this->tMargin);
  if ($this->ColActive) { $this->pgwidth = $this->ColWidth; }	// *COLUMNS*

  $this->processingHeader=false;
}



/*-- TABLES --*/
function TableHeaderFooter($content='',$tablestartpage='',$tablestartcolumn ='',$horf = 'H',$level, $firstSpread=true, $finalSpread=true) {
  if(($horf=='H' || $horf=='F') && !empty($content) && !empty($content[0])) {	// mPDF 5.6.61
	$table = &$this->table[1][1];
	// Advance down page by half width of top border

	if ($horf=='H') { // Only if header
		if ($table['borders_separate']) { $adv = $table['border_spacing_V']/2 + $table['border_details']['T']['w'] + $table['padding']['T'];  }
		else { $adv = $table['max_cell_border_width']['T'] /2 ; }
		if ($adv) { 
		   if ($this->table_rotate) {
			$this->y += ($adv);
		   }
		   else {
			$this->DivLn($adv,$this->blklvl,true); 
		   }
		}
	}

   if ($horf=='F') { // Table Footer
	$firstrow = count($table['cells']) - $table['footernrows'];
	$lastrow = count($table['cells']) - 1;
   }
   else { 	// Table Header
	$firstrow = 0;
	$lastrow = $table['headernrows'] - 1;
   }

   $topy = $content[$firstrow][0]['y']-$this->y;

   for ($i=$firstrow ; $i<=$lastrow; $i++) {

    $y = $this->y;

/*-- COLUMNS --*/
	// If outside columns, this is done in PaintDivBB
	if ($this->ColActive) {
	//OUTER FILL BGCOLOR of DIVS
	 if ($this->blklvl > 0) {
	  $firstblockfill = $this->GetFirstBlockFill();
	  if ($firstblockfill && $this->blklvl >= $firstblockfill) {
		$divh = $content[$i][0]['h'];
		$bak_x = $this->x;
		$this->DivLn($divh,-3,false);
		// Reset current block fill
		$bcor = $this->blk[$this->blklvl]['bgcolorarray'];
		$this->SetFColor($bcor);
		$this->x = $bak_x;
	  }
	 }
	}
/*-- END COLUMNS --*/

    $colctr = 0;
    foreach($content[$i] as $tablehf) {
	$colctr++;
	$y = $tablehf['y'] - $topy;
      $this->y = $y;
      //Set some cell values
      $x = $tablehf['x'];
	if (($this->mirrorMargins) && ($tablestartpage == 'ODD') && (($this->page)%2==0)) {	// EVEN
		$x = $x +$this->MarginCorrection;
	}
	else if (($this->mirrorMargins) && ($tablestartpage == 'EVEN') && (($this->page)%2==1)) {	// ODD
		$x = $x +$this->MarginCorrection;
	}
/*-- COLUMNS --*/
	// Added to correct for Columns
	if ($this->ColActive) {
	   if ($this->directionality == 'rtl') {	// *RTL*
		$x -= ($this->CurrCol - $tablestartcolumn) * ($this->ColWidth+$this->ColGap);	// *RTL*
	   }	// *RTL*
	   else {	// *RTL*
		$x += ($this->CurrCol - $tablestartcolumn) * ($this->ColWidth+$this->ColGap);
	   }	// *RTL*
	}
/*-- END COLUMNS --*/

	if ($colctr==1) { $x0 = $x; }

	// mPDF ITERATION
	if ($this->iterationCounter) {
	   foreach($tablehf['textbuffer'] AS $k=>$t) {
		if (!is_array($t[0]) && preg_match('/{iteration ([a-zA-Z0-9_]+)}/',$t[0], $m)) {	// mPDF 5.5.06
			$vname = '__'.$m[1].'_';
			if (!isset($this->$vname)) { $this->$vname = 1; }
			else { $this->$vname++; }
			$tablehf['textbuffer'][$k][0] = preg_replace('/{iteration '.$m[1].'}/', $this->$vname, $tablehf['textbuffer'][$k][0]);
		}
	   }
	}


      $w = $tablehf['w'];
      $h = $tablehf['h'];
      $va = $tablehf['va'];
      $R = $tablehf['R'];
      $mih = $tablehf['mih'];
      $border = $tablehf['border'];
      $border_details = $tablehf['border_details'];
      $padding = $tablehf['padding'];
	$this->tabletheadjustfinished = true;

      $textbuffer = $tablehf['textbuffer'];

      $align = $tablehf['a'];
      //Align
      $this->divalign=$align;
	$this->x = $x;

	if ($this->ColActive) {
		if ($table['borders_separate']) { 
		 $tablefill = isset($table['bgcolor'][-1]) ? $table['bgcolor'][-1] : 0;
		 if ($tablefill) {
  				$color = $this->ConvertColor($tablefill);
  				if ($color) {
					$xadj = ($table['border_spacing_H']/2);
					$yadj = ($table['border_spacing_V']/2);
					$wadj = $table['border_spacing_H'];
					$hadj = $table['border_spacing_V'];
 			   		if ($i == $firstrow && $horf=='H') {		// Top
						$yadj += $table['padding']['T'] + $table['border_details']['T']['w'] ;
						$hadj += $table['padding']['T'] + $table['border_details']['T']['w'] ;
			   		}
			   		if (($i == ($lastrow) || (isset($tablehf['rowspan']) && ($i+$tablehf['rowspan']) == ($lastrow+1))  || (!isset($tablehf['rowspan']) && ($i+1) == ($lastrow+1))) && $horf=='F') {	// Bottom
						$hadj += $table['padding']['B'] + $table['border_details']['B']['w'] ;
			   		}
			   		if ($colctr == 1) {		// Left
						$xadj += $table['padding']['L'] + $table['border_details']['L']['w'] ;
						$wadj += $table['padding']['L'] + $table['border_details']['L']['w'] ;
			   		}
			   		if ($colctr == count($content[$i]) ) {	// Right
						$wadj += $table['padding']['R'] + $table['border_details']['R']['w'] ;
			   		}
					$this->SetFColor($color);
					$this->Rect($x - $xadj, $y - $yadj, $w + $wadj, $h + $hadj, 'F');
				}
		 }
		}
	}

	if ($table['empty_cells']!='hide' || !empty($textbuffer) || !$table['borders_separate']) { $paintcell = true; }
	else { $paintcell = false; } 

	//Vertical align
	if ($R && INTVAL($R) > 0 && isset($va) && $va!='B') { $va='B';}

	if (!isset($va) || empty($va) || $va=='M') $this->y += ($h-$mih)/2;
      elseif (isset($va) && $va=='B') $this->y += $h-$mih;


	//TABLE ROW OR CELL FILL BGCOLOR
	$fill = 0;
	if (isset($tablehf['bgcolor']) && $tablehf['bgcolor'] && $tablehf['bgcolor']!='transparent') { 
		$fill = $tablehf['bgcolor'];
		$leveladj = 6;
	}
	else if (isset($content[$i][0]['trbgcolor']) && $content[$i][0]['trbgcolor'] && $content[$i][0]['trbgcolor']!='transparent') { // Row color
		$fill = $content[$i][0]['trbgcolor'];
		$leveladj = 3;
	}
	if ($fill && $paintcell) {
  		$color = $this->ConvertColor($fill);
  		if ($color) {
 			if ($table['borders_separate']) { 
			   if ($this->ColActive) {
				$this->SetFColor($color);
				$this->Rect($x+ ($table['border_spacing_H']/2), $y+ ($table['border_spacing_V']/2), $w- $table['border_spacing_H'], $h- $table['border_spacing_V'], 'F');
			   }
			   else {
				$this->tableBackgrounds[$level*9+$leveladj][] = array('gradient'=>false, 'x'=>($x + ($table['border_spacing_H']/2)), 'y'=>($y + ($table['border_spacing_V']/2)), 'w'=>($w - $table['border_spacing_H']), 'h'=>($h - $table['border_spacing_V']), 'col'=>$color);
			   }
			}
 			else { 
			   if ($this->ColActive) {
				$this->SetFColor($color);
				$this->Rect($x, $y, $w, $h, 'F');
			   }
			   else {
				$this->tableBackgrounds[$level*9+$leveladj][] = array('gradient'=>false, 'x'=>$x, 'y'=>$y, 'w'=>$w, 'h'=>$h, 'col'=>$color);
			   }
			}
		}
	}


/*-- BACKGROUNDS --*/
	if (isset($tablehf['gradient']) && $tablehf['gradient'] && $paintcell){
		$g = $this->grad->parseBackgroundGradient($tablehf['gradient']);
		if ($g) {
 		  if ($table['borders_separate']) { 
 			$px = $x+ ($table['border_spacing_H']/2);
			$py = $y+ ($table['border_spacing_V']/2);
			$pw = $w- $table['border_spacing_H'];
			$ph = $h- $table['border_spacing_V'];
		  }
		  else {
			$px = $x;
			$py = $y;
			$pw = $w;
			$ph = $h;
		  }
		  if ($this->ColActive) {
			$this->grad->Gradient($px, $py, $pw, $ph, $g['type'], $g['stops'], $g['colorspace'], $g['coords'], $g['extend']);
		  }
		  else {
			$this->tableBackgrounds[$level*9+7][] = array('gradient'=>true, 'x'=>$px, 'y'=>$py, 'w'=>$pw, 'h'=>$ph, 'gradtype'=>$g['type'], 'stops'=>$g['stops'], 'colorspace'=>$g['colorspace'], 'coords'=>$g['coords'], 'extend'=>$g['extend'], 'clippath'=>'');
		  }
		}
	}

	if (isset($tablehf['background-image']) && $paintcell){
	  if ($tablehf['background-image']['gradient'] && preg_match('/(-moz-)*(repeating-)*(linear|radial)-gradient/', $tablehf['background-image']['gradient'] )) {
		$g = $this->grad->parseMozGradient( $tablehf['background-image']['gradient'] );
		if ($g) {
 		  if ($table['borders_separate']) { 
 			$px = $x+ ($table['border_spacing_H']/2);
			$py = $y+ ($table['border_spacing_V']/2);
			$pw = $w- $table['border_spacing_H'];
			$ph = $h- $table['border_spacing_V'];
		  }
		  else {
			$px = $x;
			$py = $y;
			$pw = $w;
			$ph = $h;
		  }
		  if ($this->ColActive) {
			$this->grad->Gradient($px, $py, $pw, $ph, $g['type'], $g['stops'], $g['colorspace'], $g['coords'], $g['extend']);
		  }
		  else {
			$this->tableBackgrounds[$level*9+7][] = array('gradient'=>true, 'x'=>$px, 'y'=>$py, 'w'=>$pw, 'h'=>$ph, 'gradtype'=>$g['type'], 'stops'=>$g['stops'], 'colorspace'=>$g['colorspace'], 'coords'=>$g['coords'], 'extend'=>$g['extend'], 'clippath'=>'');
		  }
		}
	  }
	  else if ($tablehf['background-image']['image_id']) {	// Background pattern
		$n = count($this->patterns)+1;
 		if ($table['borders_separate']) { 
 			$px = $x+ ($table['border_spacing_H']/2);
			$py = $y+ ($table['border_spacing_V']/2);
			$pw = $w- $table['border_spacing_H'];
			$ph = $h- $table['border_spacing_V'];
		}
		else {
			$px = $x;
			$py = $y;
			$pw = $w;
			$ph = $h;
		}
		if ($this->ColActive) {
			list($orig_w, $orig_h, $x_repeat, $y_repeat) = $this->_resizeBackgroundImage($tablehf['background-image']['orig_w'], $tablehf['background-image']['orig_h'], $pw, $ph, $tablehf['background-image']['resize'], $tablehf['background-image']['x_repeat'] , $tablehf['background-image']['y_repeat']);
			$this->patterns[$n] = array('x'=>$px, 'y'=>$py, 'w'=>$pw, 'h'=>$ph, 'pgh'=>$this->h, 'image_id'=>$tablehf['background-image']['image_id'], 'orig_w'=>$orig_w, 'orig_h'=>$orig_h, 'x_pos'=>$tablehf['background-image']['x_pos'] , 'y_pos'=>$tablehf['background-image']['y_pos'] , 'x_repeat'=>$x_repeat, 'y_repeat'=>$y_repeat, 'itype'=>$tablehf['background-image']['itype']);
			if ($tablehf['background-image']['opacity']>0 && $tablehf['background-image']['opacity']<1) { $opac = $this->SetAlpha($tablehf['background-image']['opacity'],'Normal',true); }
			else { $opac = ''; }
			$this->_out(sprintf('q /Pattern cs /P%d scn %s %.3F %.3F %.3F %.3F re f Q', $n, $opac, $px*_MPDFK, ($this->h-$py)*_MPDFK, $pw*_MPDFK, -$ph*_MPDFK));
		}
		else {
			$this->tableBackgrounds[$level*9+8][] = array('x'=>$px, 'y'=>$py, 'w'=>$pw, 'h'=>$ph, 'image_id'=>$tablehf['background-image']['image_id'], 'orig_w'=>$tablehf['background-image']['orig_w'], 'orig_h'=>$tablehf['background-image']['orig_h'], 'x_pos'=>$tablehf['background-image']['x_pos'], 'y_pos'=>$tablehf['background-image']['y_pos'], 'x_repeat'=>$tablehf['background-image']['x_repeat'], 'y_repeat'=>$tablehf['background-image']['y_repeat'], 'clippath'=>'', 'resize'=>$tablehf['background-image']['resize'], 'opacity'=>$tablehf['background-image']['opacity'], 'itype'=>$tablehf['background-image']['itype']);
		}
	  }
	}
/*-- END BACKGROUNDS --*/

   	//Cell Border
 	if ($table['borders_separate'] && $paintcell && $border) { 
 		$this->_tableRect($x+ ($table['border_spacing_H']/2)+($border_details['L']['w'] /2), $y+ ($table['border_spacing_V']/2)+($border_details['T']['w'] /2), $w-$table['border_spacing_H']-($border_details['L']['w'] /2)-($border_details['R']['w'] /2), $h- $table['border_spacing_V']-($border_details['T']['w'] /2)-($border_details['B']['w']/2), $border, $border_details, false, $table['borders_separate']);
	}
 	else if ($paintcell && $border) { 
		$this->_tableRect($x, $y, $w, $h, $border, $border_details, true, $table['borders_separate']);  	// true causes buffer
	}

 	//Print cell content
      //$this->divheight = $this->table_lineheight*$this->lineheight; 
      if (!empty($textbuffer)) {
		if ($horf=='F' && preg_match('/{colsum([0-9]*)[_]*}/', $textbuffer[0][0], $m)) {
			$rep = sprintf("%01.".intval($m[1])."f", $this->colsums[$colctr-1]);
			$textbuffer[0][0] = preg_replace('/{colsum[0-9_]*}/', $rep ,$textbuffer[0][0]);
		}

		if ($R) {
					$cellPtSize = $textbuffer[0][11] / $this->shrin_k;
					if (!$cellPtSize) { $cellPtSize = $this->default_font_size; }
					$cellFontHeight = ($cellPtSize/_MPDFK);
					$opx = $this->x;
					$opy = $this->y;
					$angle = INTVAL($R);
					// Only allow 45 - 90 degrees (when bottom-aligned) or -90
					if ($angle > 90) { $angle = 90; }
					else if ($angle > 0 && (isset($va) && $va!='B')) { $angle = 90; }
					else if ($angle > 0 && $angle <45) { $angle = 45; }
					else if ($angle < 0) { $angle = -90; }
					$offset = ((sin(deg2rad($angle))) * 0.37 * $cellFontHeight);
					if (isset($align) && $align =='R') { 
						$this->x += ($w) + ($offset) - ($cellFontHeight/3) - ($padding['R'] + $border_details['R']['w']); 
					}
					else if (!isset($align ) || $align =='C') { 
						$this->x += ($w/2) + ($offset); 
					}
					else { 
						$this->x += ($offset) + ($cellFontHeight/3)+($padding['L'] + $border_details['L']['w']); 
					}
					$str = '';
					foreach($tablehf['textbuffer'] AS $t) { $str .= $t[0].' '; }
					$str = trim($str);

					if (!isset($va) || $va=='M') { 
						$this->y -= ($h-$mih)/2; //Undo what was added earlier VERTICAL ALIGN
						if ($angle > 0) { $this->y += (($h-$mih)/2)+($padding['T'] + $border_details['T']['w']) + ($mih-($padding['T'] + $border_details['T']['w']+$border_details['B']['w']+$padding['B'])); }
						else if ($angle < 0) { $this->y += (($h-$mih)/2)+($padding['T'] + $border_details['T']['w']); }
					}
					else if (isset($va) && $va=='B') { 
						$this->y -= $h-$mih; //Undo what was added earlier VERTICAL ALIGN
						if ($angle > 0) { $this->y += $h-($border_details['B']['w']+$padding['B']); }
						else if ($angle < 0) { $this->y += $h-$mih+($padding['T'] + $border_details['T']['w']); }
					}
					else if (isset($va) && $va=='T') { 
						if ($angle > 0) { $this->y += $mih-($border_details['B']['w']+$padding['B']); }
						else if ($angle < 0) { $this->y += ($padding['T'] + $border_details['T']['w']); }
					}

					$this->Rotate($angle,$this->x,$this->y);
					$s_fs = $this->FontSizePt;
					$s_f = $this->FontFamily;
					$s_st = $this->FontStyle;
					if (!empty($textbuffer[0][3])) { //Font Color
						$cor = $textbuffer[0][3];
						$this->SetTColor($cor);	
					}
					$s_str = $this->strike;
					$this->strike = $textbuffer[0][8]; //Strikethrough
					$this->SetFont($textbuffer[0][4],$textbuffer[0][2],$cellPtSize,true,true);
					$this->Text($this->x,$this->y,$str);
					$this->Rotate(0);
					$this->SetFont($s_f,$s_st,$s_fs,true,true);
					$this->SetTColor(0);
					$this->strike = $s_str;
					$this->x = $opx;
					$this->y = $opy;
		}
		else {
			if ($table['borders_separate']) {	// NB twice border width
				$xadj = $border_details['L']['w'] + $padding['L'] +($table['border_spacing_H']/2);
				$wadj = $border_details['L']['w'] + $border_details['R']['w'] + $padding['L'] +$padding['R'] + $table['border_spacing_H'];
				$yadj = $border_details['T']['w'] + $padding['T'] + ($table['border_spacing_H']/2);
			}
			else {
				$xadj = $border_details['L']['w']/2 + $padding['L'];
				$wadj = ($border_details['L']['w'] + $border_details['R']['w'])/2 + $padding['L'] + $padding['R'];
				$yadj = $border_details['T']['w']/2 + $padding['T'];
			}

			$this->divwidth=$w-($wadj);
			$this->x += $xadj;
			$this->y += $yadj;
			$this->printbuffer($textbuffer,'',true);
		}

	}
      $textbuffer = array();

/*-- BACKGROUNDS --*/
			if (!$this->ColActive) {
	  		  if (isset($content[$i][0]['trgradients']) && ($colctr==1 || $table['borders_separate'])) { 
				$g = $this->grad->parseBackgroundGradient($content[$i][0]['trgradients']);
				if ($g) {
					$gx = $x0;
					$gy = $y;
					$gh = $h;
					$gw = $table['w'] - ($table['max_cell_border_width']['L']/2) - ($table['max_cell_border_width']['R']/2) - $table['margin']['L'] - $table['margin']['R'];
					if ($table['borders_separate']) { 
						$gw -= ($table['padding']['L'] + $table['border_details']['L']['w'] + $table['padding']['R'] + $table['border_details']['R']['w'] + $table['border_spacing_H']); 
						$s = '';
 						$clx = $x+ ($table['border_spacing_H']/2);
						$cly = $y+ ($table['border_spacing_V']/2);
						$clw = $w- $table['border_spacing_H'];
						$clh = $h- $table['border_spacing_V'];
						// Set clipping path
						$s = ' q 0 w ';	// Line width=0
						$s .= sprintf('%.3F %.3F m ', ($clx)*_MPDFK, ($this->h-($cly))*_MPDFK);	// start point TL before the arc
						$s .= sprintf('%.3F %.3F l ', ($clx)*_MPDFK, ($this->h-($cly+$clh))*_MPDFK);	// line to BL
						$s .= sprintf('%.3F %.3F l ', ($clx+$clw)*_MPDFK, ($this->h-($cly+$clh))*_MPDFK);	// line to BR
						$s .= sprintf('%.3F %.3F l ', ($clx+$clw)*_MPDFK, ($this->h-($cly))*_MPDFK);	// line to TR
						$s .= sprintf('%.3F %.3F l ', ($clx)*_MPDFK, ($this->h-($cly))*_MPDFK);	// line to TL
						$s .= ' W n ';	// Ends path no-op & Sets the clipping path
						$this->tableBackgrounds[$level*9+4][] = array('gradient'=>true, 'x'=>$gx + ($table['border_spacing_H']/2), 'y'=>$gy + ($table['border_spacing_V']/2), 'w'=>$gw - $table['border_spacing_V'], 'h'=>$gh - $table['border_spacing_H'], 'gradtype'=>$g['type'], 'stops'=>$g['stops'], 'colorspace'=>$g['colorspace'], 'coords'=>$g['coords'], 'extend'=>$g['extend'], 'clippath'=>$s);
					}
					else {
						$this->tableBackgrounds[$level*9+4][] = array('gradient'=>true, 'x'=>$gx, 'y'=>$gy, 'w'=>$gw, 'h'=>$gh, 'gradtype'=>$g['type'], 'stops'=>$g['stops'], 'colorspace'=>$g['colorspace'], 'coords'=>$g['coords'], 'extend'=>$g['extend'], 'clippath'=>'');
					}
				}
			    }

			   if (isset($content[$i][0]['trbackground-images']) && ($colctr==1 || $table['borders_separate'])) { 
			    if ($content[$i][0]['trbackground-images']['gradient'] && preg_match('/(-moz-)*(repeating-)*(linear|radial)-gradient/', $content[$i][0]['trbackground-images']['gradient'] )) {
				$g = $this->grad->parseMozGradient( $content[$i][0]['trbackground-images']['gradient'] );
				if ($g) {
					$gx = $x0;
					$gy = $y;
					$gh = $h;
					$gw = $table['w'] - ($table['max_cell_border_width']['L']/2) - ($table['max_cell_border_width']['R']/2) - $table['margin']['L'] - $table['margin']['R'];
					if ($table['borders_separate']) { 
						$gw -= ($table['padding']['L'] + $table['border_details']['L']['w'] + $table['padding']['R'] + $table['border_details']['R']['w'] + $table['border_spacing_H']); 
						$s = '';
 						$clx = $x+ ($table['border_spacing_H']/2);
						$cly = $y+ ($table['border_spacing_V']/2);
						$clw = $w- $table['border_spacing_H'];
						$clh = $h- $table['border_spacing_V'];
						// Set clipping path
						$s = ' q 0 w ';	// Line width=0
						$s .= sprintf('%.3F %.3F m ', ($clx)*_MPDFK, ($this->h-($cly))*_MPDFK);	// start point TL before the arc
						$s .= sprintf('%.3F %.3F l ', ($clx)*_MPDFK, ($this->h-($cly+$clh))*_MPDFK);	// line to BL
						$s .= sprintf('%.3F %.3F l ', ($clx+$clw)*_MPDFK, ($this->h-($cly+$clh))*_MPDFK);	// line to BR
						$s .= sprintf('%.3F %.3F l ', ($clx+$clw)*_MPDFK, ($this->h-($cly))*_MPDFK);	// line to TR
						$s .= sprintf('%.3F %.3F l ', ($clx)*_MPDFK, ($this->h-($cly))*_MPDFK);	// line to TL
						$s .= ' W n ';	// Ends path no-op & Sets the clipping path
						$this->tableBackgrounds[$level*9+4][] = array('gradient'=>true, 'x'=>$gx + ($table['border_spacing_H']/2), 'y'=>$gy + ($table['border_spacing_V']/2), 'w'=>$gw - $table['border_spacing_V'], 'h'=>$gh - $table['border_spacing_H'], 'gradtype'=>$g['type'], 'stops'=>$g['stops'], 'colorspace'=>$g['colorspace'], 'coords'=>$g['coords'], 'extend'=>$g['extend'], 'clippath'=>$s);
					}
					else {
						$this->tableBackgrounds[$level*9+4][] = array('gradient'=>true, 'x'=>$gx, 'y'=>$gy, 'w'=>$gw, 'h'=>$gh, 'gradtype'=>$g['type'], 'stops'=>$g['stops'], 'colorspace'=>$g['colorspace'], 'coords'=>$g['coords'], 'extend'=>$g['extend'], 'clippath'=>'');
					}
				}
			    }
			    else { 
				$image_id = $content[$i][0]['trbackground-images']['image_id'];
				$orig_w = $content[$i][0]['trbackground-images']['orig_w'];
				$orig_h = $content[$i][0]['trbackground-images']['orig_h'];
				$x_pos = $content[$i][0]['trbackground-images']['x_pos'];
				$y_pos = $content[$i][0]['trbackground-images']['y_pos'];
				$x_repeat = $content[$i][0]['trbackground-images']['x_repeat'];
				$y_repeat = $content[$i][0]['trbackground-images']['y_repeat'];
				$resize = $content[$i][0]['trbackground-images']['resize'];
				$opacity = $content[$i][0]['trbackground-images']['opacity'];
				$itype = $content[$i][0]['trbackground-images']['itype'];

				$clippath = '';
				$gx = $x0;
				$gy = $y;
				$gh = $h;
				$gw = $table['w'] - ($table['max_cell_border_width']['L']/2) - ($table['max_cell_border_width']['R']/2) - $table['margin']['L'] - $table['margin']['R'];
				if ($table['borders_separate']) { 
					$gw -= ($table['padding']['L'] + $table['border_details']['L']['w'] + $table['padding']['R'] + $table['border_details']['R']['w'] + $table['border_spacing_H']); 
					$s = '';
 					$clx = $x+ ($table['border_spacing_H']/2);
					$cly = $y+ ($table['border_spacing_V']/2);
					$clw = $w- $table['border_spacing_H'];
					$clh = $h- $table['border_spacing_V'];
					// Set clipping path
					$s = ' q 0 w ';	// Line width=0
					$s .= sprintf('%.3F %.3F m ', ($clx)*_MPDFK, ($this->h-($cly))*_MPDFK);	// start point TL before the arc
					$s .= sprintf('%.3F %.3F l ', ($clx)*_MPDFK, ($this->h-($cly+$clh))*_MPDFK);	// line to BL
					$s .= sprintf('%.3F %.3F l ', ($clx+$clw)*_MPDFK, ($this->h-($cly+$clh))*_MPDFK);	// line to BR
					$s .= sprintf('%.3F %.3F l ', ($clx+$clw)*_MPDFK, ($this->h-($cly))*_MPDFK);	// line to TR
					$s .= sprintf('%.3F %.3F l ', ($clx)*_MPDFK, ($this->h-($cly))*_MPDFK);	// line to TL
					$s .= ' W n ';	// Ends path no-op & Sets the clipping path
					$this->tableBackgrounds[$level*9+5][] = array('x'=>$gx + ($table['border_spacing_H']/2), 'y'=>$gy + ($table['border_spacing_V']/2), 'w'=>$gw - $table['border_spacing_V'], 'h'=>$gh - $table['border_spacing_H'], 'image_id'=>$image_id, 'orig_w'=>$orig_w, 'orig_h'=>$orig_h, 'x_pos'=>$x_pos, 'y_pos'=>$y_pos, 'x_repeat'=>$x_repeat, 'y_repeat'=>$y_repeat, 'clippath'=>$s, 'resize'=>$resize, 'opacity'=>$opacity, 'itype'=>$itype);
				}
				else {
					$this->tableBackgrounds[$level*9+5][] = array('x'=>$gx, 'y'=>$gy, 'w'=>$gw, 'h'=>$gh, 'image_id'=>$image_id, 'orig_w'=>$orig_w, 'orig_h'=>$orig_h, 'x_pos'=>$x_pos, 'y_pos'=>$y_pos, 'x_repeat'=>$x_repeat, 'y_repeat'=>$y_repeat, 'clippath'=>'', 'resize'=>$resize, 'opacity'=>$opacity, 'itype'=>$itype);
				}
			    }
			   }
			}
/*-- END BACKGROUNDS --*/

	// TABLE BORDER - if separate OR collapsed and only table border
	if (($table['borders_separate'] || ($this->simpleTables && !$table['simple']['border'])) && $table['border']) { 
			$halfspaceL = $table['padding']['L'] + ($table['border_spacing_H']/2);
			$halfspaceR = $table['padding']['R'] + ($table['border_spacing_H']/2);
			$halfspaceT = $table['padding']['T'] + ($table['border_spacing_V']/2);
			$halfspaceB = $table['padding']['B'] + ($table['border_spacing_V']/2);
			$tbx = $x;
			$tby = $y;
			$tbw = $w;
			$tbh = $h;
			$tab_bord = 0;
			$corner = '';
 			if ($i == $firstrow && $horf=='H') {		// Top
				$tby -= $halfspaceT + ($table['border_details']['T']['w']/2);
				$tbh += $halfspaceT + ($table['border_details']['T']['w']/2);
				$this->setBorder($tab_bord , _BORDER_TOP); 
				$corner .= 'T';
			}
			if (($i == ($lastrow) || (isset($tablehf['rowspan']) && ($i+$tablehf['rowspan']) == ($lastrow+1))) && $horf=='F') {	// Bottom
				$tbh += $halfspaceB + ($table['border_details']['B']['w']/2);
				$this->setBorder($tab_bord , _BORDER_BOTTOM); 
				$corner .= 'B';
			}
			if ($colctr == 1 && $firstSpread) {	// Left
				$tbx -= $halfspaceL + ($table['border_details']['L']['w']/2);
				$tbw += $halfspaceL + ($table['border_details']['L']['w']/2);
				$this->setBorder($tab_bord , _BORDER_LEFT); 
				$corner .= 'L';
			}
			if ($colctr == count($content[$i]) && $finalSpread) {	// Right
				$tbw += $halfspaceR + ($table['border_details']['R']['w']/2);
				$this->setBorder($tab_bord , _BORDER_RIGHT); 
				$corner .= 'R';
			}
			$this->_tableRect($tbx, $tby, $tbw, $tbh, $tab_bord , $table['border_details'], false, $table['borders_separate'], 'table', $corner, $table['border_spacing_V'], $table['border_spacing_H'] );
	}


     }// end column $content
     $this->y = $y + $h; //Update y coordinate
   }// end row $i
   unset($table );
   $this->colsums = array();
  }
}
/*-- END TABLES --*/

/*-- HTMLHEADERS-FOOTERS --*/
function SetHTMLHeader($header='',$OE='',$write=false) {

	$height = 0;
	if (is_array($header) && isset($header['html']) && $header['html']) { 
		$Hhtml = $header['html']; 
		if ($this->setAutoTopMargin) {
			if (isset($header['h'])) { $height = $header['h']; }
			else { $height = $this->_gethtmlheight($Hhtml); }
		}
	}
	else if (!is_array($header) && $header) { 
		$Hhtml = $header; 
		if ($this->setAutoTopMargin) { $height = $this->_gethtmlheight($Hhtml); }
	}
	else { $Hhtml = ''; }

	if ($OE != 'E') { $OE = 'O'; }
	if ($OE == 'E') {
	   
	   if ($Hhtml) {
		$this->HTMLHeaderE['html'] = $Hhtml;
		$this->HTMLHeaderE['h'] = $height;
	   }
	   else { $this->HTMLHeaderE = ''; }
	}
	else {
	   
	   if ($Hhtml) {
		$this->HTMLHeader['html'] = $Hhtml;
		$this->HTMLHeader['h'] = $height;
	   }
	   else { $this->HTMLHeader = ''; }
	}
	if (!$this->mirrorMargins && $OE == 'E') { return; }
	if ($Hhtml=='') { return; }
	if ($OE == 'E') {
		$this->headerDetails['even'] = array();	// override and clear any other non-HTML header/footer
	}
	else {
		$this->headerDetails['odd'] = array();	// override and clear any non-HTML other header/footer
	}

	if ($this->setAutoTopMargin=='pad') {
		$this->tMargin = $this->margin_header + $height + $this->orig_tMargin;
		if (isset($this->saveHTMLHeader[$this->page][$OE]['mt'])) { $this->saveHTMLHeader[$this->page][$OE]['mt'] = $this->tMargin; }
	}
	else if ($this->setAutoTopMargin=='stretch') {
		$this->tMargin = max($this->orig_tMargin, $this->margin_header + $height + $this->autoMarginPadding);
		if (isset($this->saveHTMLHeader[$this->page][$OE]['mt'])) { $this->saveHTMLHeader[$this->page][$OE]['mt'] = $this->tMargin; }
	}
	if ($write && $this->state!=0 && (($this->mirrorMargins && $OE == 'E' && ($this->page)%2==0) || ($this->mirrorMargins && $OE != 'E' && ($this->page)%2==1) || !$this->mirrorMargins)) { $this->writeHTMLHeaders(); }
}

function SetHTMLFooter($footer='',$OE='') {

	$height = 0;
	if (is_array($footer) && isset($footer['html']) && $footer['html']) { 
		$Fhtml = $footer['html']; 
		if ($this->setAutoBottomMargin) {
			if (isset($footer['h'])) { $height = $footer['h']; }
			else { $height = $this->_gethtmlheight($Fhtml); }
		}
	}
	else if (!is_array($footer) && $footer) { 
		$Fhtml = $footer; 
		if ($this->setAutoBottomMargin) { $height = $this->_gethtmlheight($Fhtml); }
	}
	else { $Fhtml = ''; }

	if ($OE != 'E') { $OE = 'O'; }
	if ($OE == 'E') {
	   
	   if ($Fhtml) {
		$this->HTMLFooterE['html'] = $Fhtml;
		$this->HTMLFooterE['h'] = $height;
	   }
	   else { $this->HTMLFooterE = ''; }
	}
	else {
	   
	   if ($Fhtml) {
		$this->HTMLFooter['html'] = $Fhtml;
		$this->HTMLFooter['h'] = $height;
	   }
	   else { $this->HTMLFooter = ''; }
	}
	if (!$this->mirrorMargins && $OE == 'E') { return; }
	if ($Fhtml=='') { return false; }
	if ($OE == 'E') {
		$this->footerDetails['even'] = array();	// override and clear any other header/footer
	}
	else {
		$this->footerDetails['odd'] = array();	// override and clear any other header/footer
	}

	if ($this->setAutoBottomMargin=='pad') {
		$this->bMargin = $this->margin_footer + $height + $this->orig_bMargin;
		$this->PageBreakTrigger=$this->h-$this->bMargin ;
		if (isset($this->saveHTMLHeader[$this->page][$OE]['mb'])) { $this->saveHTMLHeader[$this->page][$OE]['mb'] = $this->bMargin; }
	}
	else if ($this->setAutoBottomMargin=='stretch') {
		$this->bMargin = max($this->orig_bMargin, $this->margin_footer + $height + $this->autoMarginPadding);
		$this->PageBreakTrigger=$this->h-$this->bMargin ;
		if (isset($this->saveHTMLHeader[$this->page][$OE]['mb'])) { $this->saveHTMLHeader[$this->page][$OE]['mb'] = $this->bMargin; }
	}
}


function _getHtmlHeight($html) {
		$save_state = $this->state;
		if($this->state==0) {
			$this->AddPage($this->CurOrientation);
		}
		$this->state = 2;
		$this->Reset();
		$this->pageoutput[$this->page] = array();
		$save_x = $this->x;
		$save_y = $this->y;
		$this->x = $this->lMargin;
		$this->y = $this->margin_header;
		$html = str_replace('{PAGENO}',$this->pagenumPrefix.$this->docPageNum($this->page).$this->pagenumSuffix,$html);
		$html = str_replace($this->aliasNbPgGp,$this->nbpgPrefix.$this->docPageNumTotal($this->page).$this->nbpgSuffix,$html );
		$html = str_replace($this->aliasNbPg,$this->page,$html );
		$html = preg_replace('/\{DATE\s+(.*?)\}/e',"date('\\1')",$html );
		$this->HTMLheaderPageLinks = array();
		$this->HTMLheaderPageAnnots = array();
		$this->HTMLheaderPageForms = array();
		$savepb = $this->pageBackgrounds;
		$this->writingHTMLheader = true;
		$this->WriteHTML($html , 4);	// parameter 4 saves output to $this->headerbuffer
		$this->writingHTMLheader = false;
		$h = ($this->y - $this->margin_header);
		$this->Reset();
		$this->pageoutput[$this->page] = array();
		$this->headerbuffer = '';
		$this->pageBackgrounds = $savepb;
		$this->x = $save_x;
		$this->y = $save_y;
		$this->state = $save_state;
		if($save_state==0) {
			unset($this->pages[1]);
			$this->page = 0;
		}
		return $h;
}


// Called internally from Header
function writeHTMLHeaders() {

	if ($this->mirrorMargins && ($this->page)%2==0) { $OE = 'E'; }	// EVEN
	else { $OE = 'O'; }
	if ($OE == 'E') {
		$this->saveHTMLHeader[$this->page][$OE]['html'] = $this->HTMLHeaderE['html'] ;
	}
	else {
		$this->saveHTMLHeader[$this->page][$OE]['html'] = $this->HTMLHeader['html'] ;
	}
	if ($this->forcePortraitHeaders && $this->CurOrientation=='L' && $this->CurOrientation!=$this->DefOrientation) {
		$this->saveHTMLHeader[$this->page][$OE]['rotate'] = true;
		$this->saveHTMLHeader[$this->page][$OE]['ml'] = $this->tMargin;
		$this->saveHTMLHeader[$this->page][$OE]['mr'] = $this->bMargin;
		$this->saveHTMLHeader[$this->page][$OE]['mh'] = $this->margin_header;
		$this->saveHTMLHeader[$this->page][$OE]['mf'] = $this->margin_footer;
		$this->saveHTMLHeader[$this->page][$OE]['pw'] = $this->h;
		$this->saveHTMLHeader[$this->page][$OE]['ph'] = $this->w;
	}
	else {
		$this->saveHTMLHeader[$this->page][$OE]['ml'] = $this->lMargin;
		$this->saveHTMLHeader[$this->page][$OE]['mr'] = $this->rMargin;
		$this->saveHTMLHeader[$this->page][$OE]['mh'] = $this->margin_header;
		$this->saveHTMLHeader[$this->page][$OE]['mf'] = $this->margin_footer;
		$this->saveHTMLHeader[$this->page][$OE]['pw'] = $this->w;
		$this->saveHTMLHeader[$this->page][$OE]['ph'] = $this->h;
	}
}

function writeHTMLFooters() {

	if ($this->mirrorMargins && ($this->page)%2==0) { $OE = 'E'; }	// EVEN
	else { $OE = 'O'; }
	if ($OE == 'E') {
		$this->saveHTMLFooter[$this->page][$OE]['html'] = $this->HTMLFooterE['html'] ;
	}
	else {
		$this->saveHTMLFooter[$this->page][$OE]['html'] = $this->HTMLFooter['html'] ;
	}
	if ($this->forcePortraitHeaders && $this->CurOrientation=='L' && $this->CurOrientation!=$this->DefOrientation) {
		$this->saveHTMLFooter[$this->page][$OE]['rotate'] = true;
		$this->saveHTMLFooter[$this->page][$OE]['ml'] = $this->tMargin;
		$this->saveHTMLFooter[$this->page][$OE]['mr'] = $this->bMargin;
		$this->saveHTMLFooter[$this->page][$OE]['mt'] = $this->rMargin;
		$this->saveHTMLFooter[$this->page][$OE]['mb'] = $this->lMargin;
		$this->saveHTMLFooter[$this->page][$OE]['mh'] = $this->margin_header;
		$this->saveHTMLFooter[$this->page][$OE]['mf'] = $this->margin_footer;
		$this->saveHTMLFooter[$this->page][$OE]['pw'] = $this->h;
		$this->saveHTMLFooter[$this->page][$OE]['ph'] = $this->w;
	}
	else {
		$this->saveHTMLFooter[$this->page][$OE]['ml'] = $this->lMargin;
		$this->saveHTMLFooter[$this->page][$OE]['mr'] = $this->rMargin;
		$this->saveHTMLFooter[$this->page][$OE]['mt'] = $this->tMargin;
		$this->saveHTMLFooter[$this->page][$OE]['mb'] = $this->bMargin;
		$this->saveHTMLFooter[$this->page][$OE]['mh'] = $this->margin_header;
		$this->saveHTMLFooter[$this->page][$OE]['mf'] = $this->margin_footer;
		$this->saveHTMLFooter[$this->page][$OE]['pw'] = $this->w;
		$this->saveHTMLFooter[$this->page][$OE]['ph'] = $this->h;
	}
}
/*-- END HTMLHEADERS-FOOTERS --*/

function DefHeaderByName($name,$arr) {
	if (!$name) { $name = '_default'; }
	$this->pageheaders[$name] = $arr;
}

function DefFooterByName($name,$arr) {
	if (!$name) { $name = '_default'; }
	$this->pagefooters[$name] = $arr;
}

function SetHeaderByName($name,$side='O',$write=false) {
	if (!$name) { $name = '_default'; }
	if ($side=='E') { $this->headerDetails['even'] = $this->pageheaders[$name]; }
	else { $this->headerDetails['odd'] = $this->pageheaders[$name]; }
	if ($write) { $this->Header(); }
}

function SetFooterByName($name,$side='O') {
	if (!$name) { $name = '_default'; }
	if ($side=='E') { $this->footerDetails['even'] = $this->pagefooters[$name]; }
	else { $this->footerDetails['odd'] = $this->pagefooters[$name]; }
}

/*-- HTMLHEADERS-FOOTERS --*/
function DefHTMLHeaderByName($name,$html) {
	if (!$name) { $name = '_default'; }

	$this->pageHTMLheaders[$name]['html'] = $html;
	$this->pageHTMLheaders[$name]['h'] = $this->_gethtmlheight($html);
}

function DefHTMLFooterByName($name,$html) {
	if (!$name) { $name = '_default'; }

	$this->pageHTMLfooters[$name]['html'] = $html;
	$this->pageHTMLfooters[$name]['h'] = $this->_gethtmlheight($html);
}

function SetHTMLHeaderByName($name,$side='O',$write=false) {
	if (!$name) { $name = '_default'; }
	$this->SetHTMLHeader($this->pageHTMLheaders[$name],$side,$write);
}

function SetHTMLFooterByName($name,$side='O') {
	if (!$name) { $name = '_default'; }
	$this->SetHTMLFooter($this->pageHTMLfooters[$name],$side,$write);
}
/*-- END HTMLHEADERS-FOOTERS --*/


function SetHeader($Harray=array(),$side='',$write=false) {
  if (is_string($Harray)) {
    if (strlen($Harray)==0) {
	if ($side=='O') { $this->headerDetails['odd'] = array(); }
	else if ($side=='E') { $this->headerDetails['even'] = array(); }
	else { $this->headerDetails = array(); }
   }
   else if (strpos($Harray,'|') || strpos($Harray,'|')===0) {
	$hdet = explode('|',$Harray);
	$this->headerDetails = array (
  		'odd' => array (
	'L' => array ('content' => $hdet[0], 'font-size' => $this->defaultheaderfontsize, 'font-style' => $this->defaultheaderfontstyle),
	'C' => array ('content' => $hdet[1], 'font-size' => $this->defaultheaderfontsize, 'font-style' => $this->defaultheaderfontstyle),
	'R' => array ('content' => $hdet[2], 'font-size' => $this->defaultheaderfontsize, 'font-style' => $this->defaultheaderfontstyle),
	'line' => $this->defaultheaderline,
  		),
  		'even' => array (
	'R' => array ('content' => $hdet[0], 'font-size' => $this->defaultheaderfontsize, 'font-style' => $this->defaultheaderfontstyle),
	'C' => array ('content' => $hdet[1], 'font-size' => $this->defaultheaderfontsize, 'font-style' => $this->defaultheaderfontstyle),
	'L' => array ('content' => $hdet[2], 'font-size' => $this->defaultheaderfontsize, 'font-style' => $this->defaultheaderfontstyle),
	'line' => $this->defaultheaderline,
		)
	);
    }
    else {
	$this->headerDetails = array (
  		'odd' => array (
	'R' => array ('content' => $Harray, 'font-size' => $this->defaultheaderfontsize, 'font-style' => $this->defaultheaderfontstyle),
	'line' => $this->defaultheaderline,
  		),
  		'even' => array (
	'L' => array ('content' => $Harray, 'font-size' => $this->defaultheaderfontsize, 'font-style' => $this->defaultheaderfontstyle),
	'line' => $this->defaultheaderline,
		)
	);
    }
  }
  else if (is_array($Harray)) {
	if ($side=='O') { $this->headerDetails['odd'] = $Harray; }
	else if ($side=='E') { $this->headerDetails['even'] = $Harray; }
	else { $this->headerDetails = $Harray; }
  }
/*-- HTMLHEADERS-FOOTERS --*/
  // Overwrite any HTML Header previously set
  if ($side=='E') { $this->SetHTMLHeader('','E'); }
  else if ($side=='O') {  $this->SetHTMLHeader(''); }
  else {
	$this->SetHTMLHeader('');
	$this->SetHTMLHeader('','E');
  }
/*-- END HTMLHEADERS-FOOTERS --*/

  if ($write) { 
	$save_y = $this->y;
	$this->Header();
	$this->SetY($save_y) ; 
  }
}




function SetFooter($Farray=array(),$side='') {
  if (is_string($Farray)) {
    if (strlen($Farray)==0) {
	if ($side=='O') { $this->footerDetails['odd'] = array(); }
	else if ($side=='E') { $this->footerDetails['even'] = array(); }
	else { $this->footerDetails = array(); }
    }
    else if (strpos($Farray,'|') || strpos($Farray,'|')===0) {
	$fdet = explode('|',$Farray);
	$this->footerDetails = array (
		'odd' => array (
	'L' => array ('content' => $fdet[0], 'font-size' => $this->defaultfooterfontsize, 'font-style' => $this->defaultfooterfontstyle),
	'C' => array ('content' => $fdet[1], 'font-size' => $this->defaultfooterfontsize, 'font-style' => $this->defaultfooterfontstyle),
	'R' => array ('content' => $fdet[2], 'font-size' => $this->defaultfooterfontsize, 'font-style' => $this->defaultfooterfontstyle),
	'line' => $this->defaultfooterline,
		),
		'even' => array (
	'R' => array ('content' => $fdet[0], 'font-size' => $this->defaultfooterfontsize, 'font-style' => $this->defaultfooterfontstyle),
	'C' => array ('content' => $fdet[1], 'font-size' => $this->defaultfooterfontsize, 'font-style' => $this->defaultfooterfontstyle),
	'L' => array ('content' => $fdet[2], 'font-size' => $this->defaultfooterfontsize, 'font-style' => $this->defaultfooterfontstyle),
	'line' => $this->defaultfooterline,
		)
	);
    }
    else {
	$this->footerDetails = array (
		'odd' => array (
	'R' => array ('content' => $Farray, 'font-size' => $this->defaultfooterfontsize, 'font-style' => $this->defaultfooterfontstyle),
	'line' => $this->defaultfooterline,
		),
		'even' => array (
	'L' => array ('content' => $Farray, 'font-size' => $this->defaultfooterfontsize, 'font-style' => $this->defaultfooterfontstyle),
	'line' => $this->defaultfooterline,
		)
	);
    }
  }
  else if (is_array($Farray)) {
	if ($side=='O') { $this->footerDetails['odd'] = $Farray; }
	else if ($side=='E') { $this->footerDetails['even'] = $Farray; }
	else { $this->footerDetails = $Farray; }
  }
/*-- HTMLHEADERS-FOOTERS --*/
  // Overwrite any HTML Footer previously set
  if ($side=='E') { $this->SetHTMLFooter('','E'); }
  else if ($side=='O') {  $this->SetHTMLFooter(''); }
  else {
	$this->SetHTMLFooter('');
	$this->SetHTMLFooter('','E');
  }
/*-- END HTMLHEADERS-FOOTERS --*/
}

/*-- WATERMARK --*/
function setUnvalidatedText($txt='', $alpha=-1) {
	if ($alpha>=0) $this->watermarkTextAlpha = $alpha;
	$this->watermarkText = $txt;
}
function SetWatermarkText($txt='', $alpha=-1) {
	if ($alpha>=0) $this->watermarkTextAlpha = $alpha;
	$this->watermarkText = $txt;
}

function SetWatermarkImage($src, $alpha=-1, $size='D', $pos='F') {
	if ($alpha>=0) $this->watermarkImageAlpha = $alpha;
	$this->watermarkImage = $src;
	$this->watermark_size = $size;
	$this->watermark_pos = $pos;
}
/*-- END WATERMARK --*/


//Page footer
function Footer() {
/*-- CSS-PAGE --*/
  // PAGED MEDIA - CROP / CROSS MARKS from @PAGE
  if ($this->show_marks == 'CROP' || $this->show_marks == 'CROPCROSS') {
	// Show TICK MARKS
	$this->SetLineWidth(0.1);	// = 0.1 mm
	$this->SetDColor($this->ConvertColor(0));
	$l = $this->cropMarkLength;
	$m = $this->cropMarkMargin;	// Distance of crop mark from margin 
	$b = $this->nonPrintMargin;	// Non-printable border at edge of paper sheet 
	$ax1 = $b;
	$bx = $this->page_box['outer_width_LR'] - $m;
	$ax = max($ax1, $bx-$l);
	$cx1 = $this->w - $b;
	$dx = $this->w - $this->page_box['outer_width_LR'] + $m;
	$cx = min($cx1, $dx+$l);
	$ay1 = $b;
	$by = $this->page_box['outer_width_TB'] - $m;
	$ay = max($ay1, $by-$l);
	$cy1 = $this->h - $b;
	$dy = $this->h - $this->page_box['outer_width_TB'] + $m;
	$cy = min($cy1, $dy+$l);

	$this->Line($ax, $this->page_box['outer_width_TB'], $bx, $this->page_box['outer_width_TB']);
	$this->Line($cx, $this->page_box['outer_width_TB'], $dx, $this->page_box['outer_width_TB']);
	$this->Line($ax, $this->h - $this->page_box['outer_width_TB'], $bx, $this->h - $this->page_box['outer_width_TB']);
	$this->Line($cx, $this->h - $this->page_box['outer_width_TB'], $dx, $this->h - $this->page_box['outer_width_TB']);
	$this->Line($this->page_box['outer_width_LR'], $ay, $this->page_box['outer_width_LR'], $by);
	$this->Line($this->page_box['outer_width_LR'], $cy, $this->page_box['outer_width_LR'], $dy);
	$this->Line($this->w - $this->page_box['outer_width_LR'], $ay, $this->w - $this->page_box['outer_width_LR'], $by);
	$this->Line($this->w - $this->page_box['outer_width_LR'], $cy, $this->w - $this->page_box['outer_width_LR'], $dy);

	if ($this->printers_info) {
		$hd = date('Y-m-d H:i').'  Page '.$this->page.' of {nb}';
		$this->SetTColor($this->ConvertColor(0));
		$this->SetFont('arial','',7.5,true,true);
		$this->x = $this->page_box['outer_width_LR'] + 1.5;
		$this->y = 1;
		$this->Cell($headerpgwidth ,$this->FontSize,$hd,0,0,'L',0,'',0,0,0,'M');
		$this->SetFont($this->default_font,'',$this->original_default_font_size);
	}

  }
  if ($this->show_marks == 'CROSS' || $this->show_marks == 'CROPCROSS') {
	$this->SetLineWidth(0.1);	// = 0.1 mm
	$this->SetDColor($this->ConvertColor(0));
	$l = 14 /2;	// longer length of the cross line (half)
	$w = 6 /2;	// shorter width of the cross line (half)
	$r = 1.2;	// radius of circle
	$m = $this->crossMarkMargin;	// Distance of cross mark from margin 
	$x1 = $this->page_box['outer_width_LR'] - $m;
	$x2 = $this->w - $this->page_box['outer_width_LR'] + $m;
	$y1 = $this->page_box['outer_width_TB'] - $m;
	$y2 = $this->h - $this->page_box['outer_width_TB'] + $m;
	// Left
	$this->Circle($x1, $this->h/2, $r, 'S') ;
	$this->Line($x1-$w, $this->h/2, $x1+$w, $this->h/2);
	$this->Line($x1, $this->h/2-$l, $x1, $this->h/2+$l);
	// Right
	$this->Circle($x2, $this->h/2, $r, 'S') ;
	$this->Line($x2-$w, $this->h/2, $x2+$w, $this->h/2);
	$this->Line($x2, $this->h/2-$l, $x2, $this->h/2+$l);
	// Top
	$this->Circle($this->w/2, $y1, $r, 'S') ;
	$this->Line($this->w/2, $y1-$w, $this->w/2, $y1+$w);
	$this->Line($this->w/2-$l, $y1, $this->w/2+$l, $y1);
	// Bottom
	$this->Circle($this->w/2, $y2, $r, 'S') ;
	$this->Line($this->w/2, $y2-$w, $this->w/2, $y2+$w);
	$this->Line($this->w/2-$l, $y2, $this->w/2+$l, $y2);
  }


	// If @page set non-HTML headers/footers named, they were not read until later in the HTML code - so now set them
	if ($this->page==1) {
		if ($this->firstPageBoxHeader) {
			$this->headerDetails['odd'] = $this->pageheaders[$this->firstPageBoxHeader]; 
  			$this->Header();
		}
		if ($this->firstPageBoxFooter) {
			$this->footerDetails['odd'] = $this->pagefooters[$this->firstPageBoxFooter];
		}
		$this->firstPageBoxHeader='';
		$this->firstPageBoxFooter='';
	}
/*-- END CSS-PAGE --*/



/*-- HTMLHEADERS-FOOTERS --*/
  if (($this->mirrorMargins && ($this->page%2==0) && $this->HTMLFooterE) || ($this->mirrorMargins && ($this->page%2==1) && $this->HTMLFooter) || (!$this->mirrorMargins && $this->HTMLFooter)) {
	$this->writeHTMLFooters(); 
/*-- WATERMARK --*/
  	if (($this->watermarkText) && ($this->showWatermarkText)) {
		$this->watermark( $this->watermarkText, 45, 120, $this->watermarkTextAlpha);	// Watermark text
  	}
	if (($this->watermarkImage) && ($this->showWatermarkImage)) {
		$this->watermarkImg( $this->watermarkImage, $this->watermarkImageAlpha);	// Watermark image
	}
/*-- END WATERMARK --*/
	return;
  }
/*-- END HTMLHEADERS-FOOTERS --*/

  $this->processingHeader=true;
  $this->ResetMargins();	// necessary after columns
  $this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
/*-- WATERMARK --*/
  if (($this->watermarkText) && ($this->showWatermarkText)) {
	$this->watermark( $this->watermarkText, 45, 120, $this->watermarkTextAlpha);	// Watermark text
  }
  if (($this->watermarkImage) && ($this->showWatermarkImage)) {
	$this->watermarkImg( $this->watermarkImage, $this->watermarkImageAlpha);	// Watermark image
  }
/*-- END WATERMARK --*/
  $h = $this->footerDetails;
  if(count($h)) {

	if ($this->forcePortraitHeaders && $this->CurOrientation=='L' && $this->CurOrientation!=$this->DefOrientation) {
		$this->_out(sprintf('q 0 -1 1 0 0 %.3F cm ',($this->h*_MPDFK)));
		$headerpgwidth = $this->h - $this->orig_lMargin - $this->orig_rMargin;
		if (($this->mirrorMargins) && (($this->page)%2==0)) {	// EVEN
			$headerlmargin = $this->orig_rMargin;
		}
		else {
			$headerlmargin = $this->orig_lMargin;
		}
	}
	else { 
		$yadj = 0; 
		$headerpgwidth = $this->pgwidth;
		$headerlmargin = $this->lMargin;
	}
	$this->SetY(-$this->margin_footer);

	$this->SetTColor($this->ConvertColor(0));
    	$this->SUP = false;
	$this->SUB = false;
	$this->bullet = false;

	// only show pagenumber if numbering on
	$pgno = $this->docPageNum($this->page, true); 

	if (($this->mirrorMargins) && (($this->page)%2==0)) {	// EVEN
			$side = 'even';
	}
	else {	// ODD	// OR NOT MIRRORING MARGINS/FOOTERS = DEFAULT
			$side = 'odd';
	}
	$maxfontheight = 0;
	foreach(array('L','C','R') AS $pos) {
	  if (isset($h[$side][$pos]['content']) && $h[$side][$pos]['content']) {
		if (isset($h[$side][$pos]['font-size']) && $h[$side][$pos]['font-size']) { $hfsz = $h[$side][$pos]['font-size']; }
		else { $hfsz = $this->default_font_size; }
		$maxfontheight = max($maxfontheight,$hfsz);
	  }
	}
	// LEFT-CENTER-RIGHT
	foreach(array('L','C','R') AS $pos) {
	  if (isset($h[$side][$pos]['content']) && $h[$side][$pos]['content']) {
		$hd = str_replace('{PAGENO}',$pgno,$h[$side][$pos]['content']);
		$hd = str_replace($this->aliasNbPgGp,$this->nbpgPrefix.$this->aliasNbPgGp.$this->nbpgSuffix,$hd);
		$hd = preg_replace('/\{DATE\s+(.*?)\}/e',"date('\\1')",$hd);
		if (isset($h[$side][$pos]['font-family']) && $h[$side][$pos]['font-family']) { $hff = $h[$side][$pos]['font-family']; }
		else { $hff = $this->original_default_font; }
		if (isset($h[$side][$pos]['font-size']) && $h[$side][$pos]['font-size']) { $hfsz = $h[$side][$pos]['font-size']; }
		else { $hfsz = $this->original_default_font_size; }
		$maxfontheight = max($maxfontheight,$hfsz);
		if (isset($h[$side][$pos]['font-style']) && $h[$side][$pos]['font-style']) { $hfst = $h[$side][$pos]['font-style']; }
		else { $hfst = ''; }
		if (isset($h[$side][$pos]['color']) && $h[$side][$pos]['color']) { 
			$hfcol = $h[$side][$pos]['color']; 
			$cor = $this->ConvertColor($hfcol);
			if ($cor) { $this->SetTColor($cor); }
		}
		else { $hfcol = ''; }
		$this->SetFont($hff,$hfst,$hfsz,true,true);
		$this->x = $headerlmargin ;
		$this->y = $this->h - $this->margin_footer - ($maxfontheight/_MPDFK);
		$hd = $this->purify_utf8_text($hd);
		if ($this->text_input_as_HTML) {
			$hd = $this->all_entities_to_utf8($hd);
		}
		// CONVERT CODEPAGE
		if ($this->usingCoreFont) { $hd = mb_convert_encoding($hd,$this->mb_enc,'UTF-8'); }
		// DIRECTIONALITY RTL
		$this->magic_reverse_dir($hd, true, $this->directionality);	// *RTL*
		// Font-specific ligature substitution for Indic fonts
		if (isset($this->CurrentFont['indic']) && $this->CurrentFont['indic']) $this->ConvertIndic($hd);	// *INDIC*
		$align = $pos;
		if ($this->directionality == 'rtl') { 
			if ($pos == 'L') { $align = 'R'; }
			else if ($pos == 'R') { $align = 'L'; }
		}

		if ($pos!='L' && (strpos($hd,$this->aliasNbPg)!==false || strpos($hd,$this->aliasNbPgGp)!==false)) { 
			if (strpos($hd,$this->aliasNbPgGp)!==false) { $type= 'nbpggp'; } else { $type= 'nbpg'; }
			$this->_out('{mpdfheader'.$type.' '.$pos.' ff='.$hff.' fs='.$hfst.' fz='.$hfsz.'}'); 
			$this->Cell($headerpgwidth ,$maxfontheight/_MPDFK ,$hd,0,0,$align,0,'',0,0,0,'M');
			$this->_out('Q');
		}
		else { 
			$this->Cell($headerpgwidth ,$maxfontheight/_MPDFK ,$hd,0,0,$align,0,'',0,0,0,'M');
		}
		if ($hfcol) { $this->SetTColor($this->ConvertColor(0)); }
	  }
	}
	// Return Font to normal
	$this->SetFont($this->default_font,'',$this->original_default_font_size);

	// LINE

	if (isset($h[$side]['line']) && $h[$side]['line']) { 
		$this->SetLineWidth(0.1);
		$this->SetDColor($this->ConvertColor(0));
		$this->Line($headerlmargin , $this->y-($maxfontheight*($this->footer_line_spacing)/_MPDFK), $headerlmargin +$headerpgwidth, $this->y-($maxfontheight*($this->footer_line_spacing)/_MPDFK));
	}
	if ($this->forcePortraitHeaders && $this->CurOrientation=='L' && $this->CurOrientation!=$this->DefOrientation) {
		$this->_out('Q');
	}
  }
  $this->processingHeader=false;

}

///////////////////
// HYPHENATION
///////////////////
// mPDF 5.6.21
// Hard hyphens
function hardHyphenate($word, $maxWidth) {
	// Don't hyphenate web addresses
	if (preg_match('/^(http:|www\.)/',$word)) { return array(false,'','',''); }

	// Get dictionary
	$poss = array();
	$softhyphens = array();
	$offset = 0;
	$p = true;
	if ($this->usingCoreFont) {
		$wl = strlen($word);
	}
	else {
		$wl = mb_strlen($word,'UTF-8');
	}
	while($offset < $wl) {
		if (!$this->usingCoreFont) { 
			$p = mb_strpos($word, "-", $offset, 'UTF-8');
		}
		else if ($this->FontFamily!='csymbol' && $this->FontFamily!='czapfdingbats') {
			$p = strpos($word, "-", $offset);
		}
		if ($p !== false) { $poss[] = $p - count($poss); }
		else { break; }
		$offset = $p+1;
	}
	$success = false;
	foreach($poss AS $i) {
			if ($this->usingCoreFont) { 
				$a = substr($word,0,$i);
				if ($this->GetStringWidth($a.'-') > $maxWidth) { break ; }
				$pre = $a;
				$post = substr($word,$i,strlen($word));
				$prelength = strlen($pre);
			}
			else { 
				$a = mb_substr($word,0,$i,'UTF-8');
				if ($this->GetStringWidth($a.'-') > $maxWidth) { break ; }
				$pre = $a;
				$post = mb_substr($word,$i,mb_strlen($word,'UTF-8'),'UTF-8');
				$prelength = mb_strlen($pre, 'UTF-8');
			}
			$success = true;
	}
	return array($success,$pre,$post,$prelength);
}


/*-- HYPHENATION --*/
///////////////////
///////////////////
// HYPHENATION
///////////////////
// Soft hyphs
function softHyphenate($word, $maxWidth) {
	// Don't hyphenate web addresses
	if (preg_match('/^(http:|www\.)/',$word)) { return array(false,'','',''); }

	// Get dictionary
	$poss = array();
	$softhyphens = array();
	$offset = 0;
	$p = true;
	if ($this->usingCoreFont) {
		$wl = strlen($word);
	}
	else {
		$wl = mb_strlen($word,'UTF-8');
	}
	while($offset < $wl) {
		// Soft Hyphens chr(173)
		if (!$this->usingCoreFont) { 
			$p = mb_strpos($word, "\xc2\xad", $offset, 'UTF-8');
		}
		else if ($this->FontFamily!='csymbol' && $this->FontFamily!='czapfdingbats') {
			$p = strpos($word, chr(173), $offset);
		}
		if ($p !== false) { $poss[] = $p - count($poss); }
		else { break; }
		$offset = $p+1;
	}
	$success = false;
	foreach($poss AS $i) {
			if ($this->usingCoreFont) { 
				$a = substr($word,0,$i);
				if ($this->GetStringWidth($a.'-') > $maxWidth) { break ; }
				$pre = $a;
				$post = substr($word,$i,strlen($word));
				$prelength = strlen($pre);
			}
			else { 
				$a = mb_substr($word,0,$i,'UTF-8');
				if ($this->GetStringWidth($a.'-') > $maxWidth) { break ; }
				$pre = $a;
				$post = mb_substr($word,$i,mb_strlen($word,'UTF-8'),'UTF-8');
				$prelength = mb_strlen($pre, 'UTF-8');
			}
			$success = true;
	}
	return array($success,$pre,$post,$prelength);
}

///////////////////
// Word hyphenation
function hyphenateWord($word, $maxWidth) {
	// Do everything inside this function in utf-8
	// Don't hyphenate web addresses
	if (preg_match('/^(http:|www\.)/',$word)) { return array(false,'','',''); }


	// Get dictionary
	if (!$this->loadedSHYdictionary) {
		if (file_exists(_MPDF_PATH.'patterns/dictionary.txt')) {
			$this->SHYdictionary = file(_MPDF_PATH.'patterns/dictionary.txt',FILE_SKIP_EMPTY_LINES);
			foreach($this->SHYdictionary as $entry) {
				$entry = trim($entry);
				$poss = array();
				$offset = 0;
				$p = true;
				$wl = mb_strlen($entry ,'UTF-8');
				while($offset < $wl) {
					$p = mb_strpos($entry, '/', $offset, 'UTF-8');
					if ($p !== false) { $poss[] = $p - count($poss); }
					else { break; }
					$offset = $p+1;
				}
				if (count($poss)) { $this->SHYdictionaryWords[str_replace('/', '', mb_strtolower($entry))] = $poss; }
			}
		}
		$this->loadedSHYdictionary = true;
	}

	if (!in_array($this->SHYlang,$this->SHYlanguages)) { return array(false,'','',''); }
	// If no pattern loaded or not the best one
	if (count($this->SHYpatterns) < 1  || ($this->loadedSHYpatterns && $this->loadedSHYpatterns != $this->SHYlang)) {
		include(_MPDF_PATH."patterns/" . $this->SHYlang . ".php"); 
		$patterns = explode(' ', $patterns);
		$new_patterns = array();
		for($i = 0; $i < count($patterns); $i++) {
			$value = $patterns[$i];
			$new_patterns[preg_replace('/[0-9]/', '', $value)] = $value;
		}
		$this->SHYpatterns = $new_patterns;
		$this->loadedSHYpatterns = $this->SHYlang;
	}

	if ($this->usingCoreFont) { $word = mb_convert_encoding($word,'UTF-8',$this->mb_enc); }

	$prepre = '';
	$postpost = '';
	$startpunctuation = "\xc2\xab\xc2\xbf\xe2\x80\x98\xe2\x80\x9b\xe2\x80\x9c\xe2\x80\x9f";
	$endpunctuation = "\xe2\x80\x9e\xe2\x80\x9d\xe2\x80\x9a\xe2\x80\x99\xc2\xbb";
	$pre = '';
	$post = '';


	if (preg_match('/^(["\''.$startpunctuation .'])+(.{'.$this->SHYcharmin.',})$/u',$word,$m)) {
		$prepre = $m[1];
		$word = $m[2];
	}
	if (preg_match('/^(.{'.$this->SHYcharmin.',})([\'\.,;:!?"'.$endpunctuation .']+)$/u',$word,$m)) {
		$word = $m[1];
		$postpost = $m[2];
	}
	if(mb_strlen($word,'UTF-8') < $this->SHYcharmin) {
			return array(false,'','','');
	}
	$success = false;

	if(isset($this->SHYdictionaryWords[mb_strtolower($word)])) {
	   foreach($this->SHYdictionaryWords[mb_strtolower($word)] AS $i) {
			$a = $prepre . mb_substr($word,0,$i,'UTF-8');
			if ($this->usingCoreFont) { $testa = mb_convert_encoding($a,$this->mb_enc,'UTF-8'); }
			else { $testa = $a; }
			if ($this->GetStringWidth($testa.'-') > $maxWidth) { break ; }
			$pre = $a;
			$post = mb_substr($word,$i,mb_strlen($word,'UTF-8'),'UTF-8') . $postpost;
			$success = true;
	   }
	}

	if (!$success) {
	   $text_word = '_' . $word . '_';
	   $word_length = mb_strlen($text_word,'UTF-8');

	   $single_character = preg_split('//u', $text_word);

	   $text_word = mb_strtolower($text_word,'UTF-8');
	   $hyphenated_word = array();
	   $numb3rs = array('0' => true, '1' => true, '2' => true, '3' => true, '4' => true, '5' => true, '6' => true, '7' => true, '8' => true, '9' => true);
	   for($position = 0; $position <= ($word_length - $this->SHYcharmin); $position++) {
		$maxwins = min(($word_length - $position), $this->SHYcharmax);
		for($win = $this->SHYcharmin; $win <= $maxwins; $win++) {
			if(isset($this->SHYpatterns[mb_substr($text_word, $position, $win,'UTF-8')])) {
				$pattern = $this->SHYpatterns[mb_substr($text_word, $position, $win,'UTF-8')];
				$digits = 1;
				$pattern_length = mb_strlen($pattern,'UTF-8');
				for($i = 0; $i < $pattern_length; $i++) {
					$char = $pattern[$i];
					if(isset($numb3rs[$char])) {
						$zero = ($i == 0) ? $position - 1 : $position + $i - $digits;
						if(!isset($hyphenated_word[$zero]) || $hyphenated_word[$zero] != $char) $hyphenated_word[$zero] = $char;
						$digits++;
					}
				}
			}
		}
	   }

	   for($i = $this->SHYleftmin; $i <= (mb_strlen($word,'UTF-8') - $this->SHYrightmin); $i++) {
		if(isset($hyphenated_word[$i]) && $hyphenated_word[$i] % 2 != 0) {
			$a = $prepre . mb_substr($word,0,$i,'UTF-8');
			if ($this->usingCoreFont) { $testa = mb_convert_encoding($a,$this->mb_enc,'UTF-8'); }
			else { $testa = $a; }
			if ($this->GetStringWidth($testa.'-') > $maxWidth + 0.0001) { break ; }
			$pre = $a;
			$post = mb_substr($word,$i,mb_strlen($word,'UTF-8'),'UTF-8') . $postpost;
			$success = true;
		}
	   }
	}
	if ($this->usingCoreFont) { 
		$pre = mb_convert_encoding($pre,$this->mb_enc,'UTF-8'); 
		$post = mb_convert_encoding($post,$this->mb_enc,'UTF-8'); 
		$prelength = strlen($pre);
	}
	else {
		$prelength = mb_strlen($pre);
	}
	return array($success,$pre,$post,$prelength);

}
/*-- END HYPHENATION --*/


/*-- HTML-CSS --*/
///////////////////
/// HTML parser ///
///////////////////
function WriteHTML($html,$sub=0,$init=true,$close=true) {
				// $sub ADDED - 0 = default; 1=headerCSS only; 2=HTML body (parts) only; 3 - HTML parses only
				// 4 - writes HTML headers
				// $close Leaves buffers etc. in current state, so that it can continue a block etc.
				// $init - Clears and sets buffers to Top level block etc.

	if (empty($html)) { $html = ''; }
	if ($this->progressBar) { $this->UpdateProgressBar(1,0,'Parsing CSS & Headers'); }	// *PROGRESS-BAR*

	if ($init) {
		$this->headerbuffer='';
		$this->textbuffer = array();
		$this->fixedPosBlockSave = array();
	}
	if ($sub == 1) { $html = '<style> '.$html.' </style>'; }	// stylesheet only

	if ($this->allow_charset_conversion) {
		if ($sub < 1) { 
			$this->ReadCharset($html); 
		}
		if ($this->charset_in && $sub!=4) {	// mPDF 5.4.14 
			$success = iconv($this->charset_in,'UTF-8//TRANSLIT',$html); 
			if ($success) { $html = $success; }
		}
	}
	$html = $this->purify_utf8($html,false);
	if ($init) {
		$this->blklvl = 0;
		$this->lastblocklevelchange = 0;
		$this->blk = array();
		$this->initialiseBlock($this->blk[0]);
		$this->blk[0]['width'] =& $this->pgwidth;
		$this->blk[0]['inner_width'] =& $this->pgwidth;
		$this->blk[0]['blockContext'] = $this->blockContext;
	}

	$zproperties = array();
	if ($sub < 2) { 
		$this->ReadMetaTags($html); 

		// mPDF 5.6.18
		if (preg_match('/<base[^>]*href=["\']([^"\'>]*)["\']/i', $html, $m)) {
			$this->SetBasePath($m[1]);
		}
		// NB default stylesheet now in mPDF.css - read on initialising class
		$html = $this->cssmgr->ReadCSS($html);

		if ($this->useLang && !$this->usingCoreFont && preg_match('/<html [^>]*lang=[\'\"](.*?)[\'\"]/ism',$html,$m)) { 
			$html_lang = $m[1]; 
		}

		if (preg_match('/<html [^>]*dir=[\'\"]\s*rtl\s*[\'\"]/ism',$html)) { 
			$zproperties['DIRECTION'] = 'rtl'; 
		}

		// allow in-line CSS for body tag to be parsed // Get <body> tag inline CSS
		if (preg_match('/<body([^>]*)>(.*?)<\/body>/ism',$html,$m) || preg_match('/<body([^>]*)>(.*)$/ism',$html,$m)) { 
			$html = $m[2]; 
			// Changed to allow style="background: url('bg.jpg')"
			if (preg_match('/style=[\"](.*?)[\"]/ism',$m[1],$mm) || preg_match('/style=[\'](.*?)[\']/ism',$m[1],$mm)) { 
				$zproperties = $this->cssmgr->readInlineCSS($mm[1]); 
			}
			if (preg_match('/dir=[\'\"]\s*rtl\s*[\'\"]/ism',$m[1])) { 
				$zproperties['DIRECTION'] = 'rtl'; 
			}
			if (isset($html_lang) && $html_lang) { $zproperties['LANG'] = $html_lang; }
			if ($this->useLang && !$this->onlyCoreFonts && preg_match('/lang=[\'\"](.*?)[\'\"]/ism',$m[1],$mm)) {
				$zproperties['LANG'] = $mm[1]; 
			}

		}
	}
	$properties = $this->cssmgr->MergeCSS('BLOCK','BODY',''); 
	if ($zproperties) { $properties = $this->cssmgr->array_merge_recursive_unique($properties,$zproperties); }

	if (isset($properties['DIRECTION']) && $properties['DIRECTION']) {
		$this->cssmgr->CSS['BODY']['DIRECTION'] = $properties['DIRECTION'];   
	}
	if (!isset($this->cssmgr->CSS['BODY']['DIRECTION'])) {
		$this->cssmgr->CSS['BODY']['DIRECTION'] = $this->directionality;   
	}
	else { $this->SetDirectionality($this->cssmgr->CSS['BODY']['DIRECTION']); }   

	$this->setCSS($properties,'','BODY'); 
	$this->blk[0]['InlineProperties'] = $this->saveInlineProperties();

	if ($sub == 1) { return ''; }
	if (!isset($this->cssmgr->CSS['BODY'])) { $this->cssmgr->CSS['BODY'] = array(); }

/*-- BACKGROUNDS --*/
	if (isset($properties['BACKGROUND-GRADIENT'])) { 
		$this->bodyBackgroundGradient = $properties['BACKGROUND-GRADIENT'];
	}

	if (isset($properties['BACKGROUND-IMAGE']) && $properties['BACKGROUND-IMAGE']) {
		$ret = $this->SetBackground($properties, $this->pgwidth);
		if ($ret) { $this->bodyBackgroundImage = $ret; }
	}
/*-- END BACKGROUNDS --*/

/*-- CSS-PAGE --*/
	// If page-box is set
	if ($this->state==0 && isset($this->cssmgr->CSS['@PAGE']) && $this->cssmgr->CSS['@PAGE'] ) {
		$this->page_box['current'] = ''; 
		$this->page_box['using'] = true;
		list($pborientation,$pbmgl,$pbmgr,$pbmgt,$pbmgb,$pbmgh,$pbmgf,$hname,$fname,$bg,$resetpagenum,$pagenumstyle,$suppress,$marks,$newformat) = $this->SetPagedMediaCSS('', false, 'O');
		$this->DefOrientation = $this->CurOrientation = $pborientation; 
		$this->orig_lMargin = $this->DeflMargin = $pbmgl; 
		$this->orig_rMargin = $this->DefrMargin = $pbmgr; 
		$this->orig_tMargin = $this->tMargin = $pbmgt;
		$this->orig_bMargin = $this->bMargin = $pbmgb;
		$this->orig_hMargin = $this->margin_header = $pbmgh;
		$this->orig_fMargin = $this->margin_footer = $pbmgf;
		list($pborientation,$pbmgl,$pbmgr,$pbmgt,$pbmgb,$pbmgh,$pbmgf,$hname,$fname,$bg,$resetpagenum,$pagenumstyle,$suppress,$marks,$newformat) = $this->SetPagedMediaCSS('', true, 'O');	// first page
		$this->show_marks = $marks;
		if ($hname && !preg_match('/^html_(.*)$/i',$hname)) $this->firstPageBoxHeader = $hname;
		if ($fname && !preg_match('/^html_(.*)$/i',$fname)) $this->firstPageBoxFooter = $fname;
	}
/*-- END CSS-PAGE --*/

	$parseonly = false; 
	$this->bufferoutput = false; 
	if ($sub == 3) { 
		$parseonly = true; 
		// Close any open block tags
		for ($b= $this->blklvl;$b>0;$b--) { $this->CloseTag($this->blk[$b]['tag']); }
		// Output any text left in buffer
		if (count($this->textbuffer)) { $this->printbuffer($this->textbuffer); }
		$this->textbuffer=array();
	} 
	else if ($sub == 4) { 
		// Close any open block tags
		for ($b= $this->blklvl;$b>0;$b--) { $this->CloseTag($this->blk[$b]['tag']); }
		// Output any text left in buffer
		if (count($this->textbuffer)) { $this->printbuffer($this->textbuffer); }
		$this->bufferoutput = true; 
		$this->textbuffer=array();
		$this->headerbuffer='';
		$properties = $this->cssmgr->MergeCSS('BLOCK','BODY','');
		$this->setCSS($properties,'','BODY'); 
	} 

	mb_internal_encoding('UTF-8'); 

	$html = $this->AdjustHTML($html, $this->tabSpaces); //Try to make HTML look more like XHTML

	if ($this->autoFontGroups) { $html = $this->AutoFont($html); }

/*-- HTMLHEADERS-FOOTERS --*/
	preg_match_all('/<htmlpageheader([^>]*)>(.*?)<\/htmlpageheader>/si',$html,$h);
	for($i=0;$i<count($h[1]);$i++) {
		if (preg_match('/name=[\'|\"](.*?)[\'|\"]/',$h[1][$i],$n)) {
			$this->pageHTMLheaders[$n[1]]['html'] = $h[2][$i]; 
			$this->pageHTMLheaders[$n[1]]['h'] = $this->_gethtmlheight($h[2][$i]); 
		}
	}
	preg_match_all('/<htmlpagefooter([^>]*)>(.*?)<\/htmlpagefooter>/si',$html,$f);
	for($i=0;$i<count($f[1]);$i++) {
		if (preg_match('/name=[\'|\"](.*?)[\'|\"]/',$f[1][$i],$n)) {
			$this->pageHTMLfooters[$n[1]]['html'] = $f[2][$i]; 
			$this->pageHTMLfooters[$n[1]]['h'] = $this->_gethtmlheight($f[2][$i]); 
		}
	}
/*-- END HTMLHEADERS-FOOTERS --*/
	$html = preg_replace('/<htmlpageheader.*?<\/htmlpageheader>/si','',$html);
	$html = preg_replace('/<htmlpagefooter.*?<\/htmlpagefooter>/si','',$html);

	if($this->state==0 && $sub!=1 && $sub!=3 && $sub!=4) {
		$this->AddPage($this->CurOrientation);
	}


/*-- HTMLHEADERS-FOOTERS --*/

	if (isset($hname) && preg_match('/^html_(.*)$/i',$hname,$n)) $this->SetHTMLHeader($this->pageHTMLheaders[$n[1]],'O',true);
	if (isset($fname) && preg_match('/^html_(.*)$/i',$fname,$n)) $this->SetHTMLFooter($this->pageHTMLfooters[$n[1]],'O');

/*-- END HTMLHEADERS-FOOTERS --*/

	$html=str_replace('<?','< ',$html); //Fix '<?XML' bug from HTML code generated by MS Word

	$this->checkSIP = false;
	$this->checkSMP = false; 
	$this->checkCJK = false; 
	if ($this->onlyCoreFonts) { $html = $this->SubstituteChars($html); }
	else {
		if (preg_match("/([".$this->pregRTLchars."])/u", $html)) { $this->biDirectional = true; }	// *RTL*
		if (preg_match("/([\x{20000}-\x{2FFFF}])/u", $html)) { $this->checkSIP = true; }
		if (preg_match("/([\x{10000}-\x{1FFFF}])/u", $html)) { $this->checkSMP = true; }
/*-- CJK-FONTS --*/
		if (preg_match("/([".$this->pregCJKchars."])/u", $html)) { $this->checkCJK = true; }
/*-- END CJK-FONTS --*/
	}

	// Don't allow non-breaking spaces that are converted to substituted chars or will break anyway and mess up table width calc.
	$html = str_replace('<tta>160</tta>',chr(32),$html); 
	$html = str_replace('</tta><tta>','|',$html); 
	$html = str_replace('</tts><tts>','|',$html); 
	$html = str_replace('</ttz><ttz>','|',$html); 

	//Add new supported tags in the DisableTags function
	$html=strip_tags($html,$this->enabledtags); //remove all unsupported tags, but the ones inside the 'enabledtags' string

	//Explode the string in order to parse the HTML code
	$a=preg_split('/<(.*?)>/ms',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
	// ? more accurate regexp that allows e.g. <a name="Silly <name>">
	// if changing - also change in fn.SubstituteChars()
	// $a = preg_split ('/<((?:[^<>]+(?:"[^"]*"|\'[^\']*\')?)+)>/ms', $html, -1, PREG_SPLIT_DELIM_CAPTURE);

	if ($this->mb_enc) { 
		mb_internal_encoding($this->mb_enc); 
	}
	$pbc = 0;
	if ($this->progressBar) { $this->UpdateProgressBar(1,0); }	// *PROGRESS-BAR*
	$this->subPos = -1;
	$cnt = count($a);
	for($i=0;$i<$cnt; $i++) {
		$e = $a[$i];
		if($i%2==0) {
		//TEXT
			if ($this->blk[$this->blklvl]['hide']) { continue; }
			if ($this->inlineDisplayOff) { continue; }
			if ($this->inMeter) { continue; }	// mPDF 5.5.09

			if ($this->inFixedPosBlock) { $this->fixedPosBlock .= $e; continue; }	// *CSS-POSITION*
			if (strlen($e) == 0) { continue; }

			$e = strcode2utf($e);
			$e = $this->lesser_entity_decode($e);

			if ($this->usingCoreFont) { 
				// If core font is selected in document which is not onlyCoreFonts - substitute with non-core font
				if ($this->useSubstitutions && !$this->onlyCoreFonts && $this->subPos<$i && !$this->specialcontent) {
					$cnt += $this->SubstituteCharsNonCore($a, $i, $e); 
				}
				// CONVERT ENCODING
				$e = mb_convert_encoding($e,$this->mb_enc,'UTF-8'); 
				// mPDF 5.6.41
				if ($this->toupper) { $e = mb_strtoupper($e,$this->mb_enc); }
				if ($this->tolower) { $e = mb_strtolower($e,$this->mb_enc); }
				if ($this->capitalize) { $e = mb_convert_case($e, MB_CASE_TITLE, "UTF-8"); }
			}
			else {
				if ($this->checkSIP && $this->CurrentFont['sipext'] && $this->subPos<$i && !$this->specialcontent) { 
					$cnt += $this->SubstituteCharsSIP($a, $i, $e); 
				}

				if ($this->useSubstitutions && !$this->onlyCoreFonts && $this->CurrentFont['type']!='Type0' && $this->subPos<$i && !$this->specialcontent) {
					// mPDF 5.6.62	removes U+200E/U+200F LTR and RTL mark and U+200C/U+200D Zero-width Joiner and Non-joiner
					$e = preg_replace("/[\xe2\x80\x8c\xe2\x80\x8d\xe2\x80\x8e\xe2\x80\x8f]/u",'',$e);
					$cnt += $this->SubstituteCharsMB($a, $i, $e); 
				}
 				if ($this->biDirectional)  { 	// *RTL*
					$e = preg_replace("/([".$this->pregRTLchars."]+)/ue", '$this->ArabJoin(stripslashes(\'\\1\'))', $e);	// *RTL*
				}	// *RTL*
				// Font-specific ligature substitution for Indic fonts
				if (isset($this->CurrentFont['indic']) && $this->CurrentFont['indic']) $this->ConvertIndic($e);	// *INDIC*

				// mPDF 5.6.62	removes U+200E/U+200F LTR and RTL mark and U+200C/U+200D Zero-width Joiner and Non-joiner
				$e = preg_replace("/[\xe2\x80\x8c\xe2\x80\x8d\xe2\x80\x8e\xe2\x80\x8f]/u",'',$e);

				if ($this->toupper) { $e = mb_strtoupper($e,$this->mb_enc); }
				if ($this->tolower) { $e = mb_strtolower($e,$this->mb_enc); }
				if ($this->capitalize) { $e = mb_convert_case($e, MB_CASE_TITLE, "UTF-8"); }
			}
			if (($this->tts) || ($this->ttz) || ($this->tta)) {
				$es = explode('|',$e);
				$e = '';
				foreach($es AS $val) {
					$e .= chr($val);
				}
			}
			//Adjust lineheight

			//  FORM ELEMENTS
  			if ($this->specialcontent) {
/*-- FORMS --*/
			   //SELECT tag (form element)
			   if ($this->specialcontent == "type=select") { 
				$e = ltrim($e); 
				$stringwidth = $this->GetStringWidth($e);
				if (!isset($this->selectoption['MAXWIDTH']) || $stringwidth > $this->selectoption['MAXWIDTH']) { $this->selectoption['MAXWIDTH'] = $stringwidth; }
				if (!isset($this->selectoption['SELECTED']) || $this->selectoption['SELECTED'] == '') { $this->selectoption['SELECTED'] = $e; }
				// mPDD 1.4 Active Forms
				if (isset($this->selectoption['ACTIVE']) && $this->selectoption['ACTIVE']) {
					$this->selectoption['ITEMS'][]=array('exportValue'=>$this->selectoption['currentVAL'], 'content'=>$e, 'selected'=>$this->selectoption['currentSEL']);
				}
			   }
			   // TEXTAREA
			   else { 
				$objattr = unserialize($this->specialcontent);
				$objattr['text'] = $e;
				$te = "\xbb\xa4\xactype=textarea,objattr=".serialize($objattr)."\xbb\xa4\xac";
				if ($this->tdbegin) {
					$this->_saveCellTextBuffer($te, $this->HREF);
				}
				else {
					$this->_saveTextBuffer($te, $this->HREF);
				}
			   }
/*-- END FORMS --*/
		      }

			// TABLE
			else if ($this->tableLevel) {
/*-- TABLES --*/
				if ($this->tdbegin) {
     				   if (($this->ignorefollowingspaces) && !$this->ispre) { $e = ltrim($e); }
				   if ($e || $e==='0') {
				      if (($this->blockjustfinished || $this->listjustfinished) && $this->cell[$this->row][$this->col]['s']>0) {
						$this->_saveCellTextBuffer("\n");
						if (!isset($this->cell[$this->row][$this->col]['maxs'])) {
							$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s']; 
						}
						elseif($this->cell[$this->row][$this->col]['maxs'] < $this->cell[$this->row][$this->col]['s']) {
							$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s'];  
						}
						$this->cell[$this->row][$this->col]['s'] = 0;// reset
				      }
					$this->blockjustfinished=false;
					$this->listjustfinished=false;

					$this->_saveCellTextBuffer($e, $this->HREF);

          				if (!isset($this->cell[$this->row][$this->col]['R']) || !$this->cell[$this->row][$this->col]['R']) {
						if (isset($this->cell[$this->row][$this->col]['s'])) { 
							$this->cell[$this->row][$this->col]['s'] += $this->GetStringWidth($e, false);
						}
						else { $this->cell[$this->row][$this->col]['s'] = $this->GetStringWidth($e, false); }
						if (!empty($this->spanborddet)) { 
							$this->cell[$this->row][$this->col]['s'] += $this->spanborddet['L']['w'] + $this->spanborddet['R']['w'];
						}
					}

					if ($this->checkCJK && preg_match("/([".$this->pregCJKchars."])/u", $e)) { $this->tableCJK = true; }	// *CJK-FONTS*

					// mPDF 5.6.13   Decimal mark alignment
					if (substr($this->cell[$this->row][$this->col]['a'],0,1) == 'D') {
						$dp = $this->decimal_align[substr($this->cell[$this->row][$this->col]['a'],0,2)];
						$s = preg_split('/'.preg_quote($dp,'/').'/', $e, 2); 	// ? needs to be /u if not core
						$s0 = $this->GetStringWidth($s[0], false);
						if ($s[1]) { $s1 = $this->GetStringWidth(($s[1].$dp), false); }
						else $s1 = 0;
						if (!isset($this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['decimal_align'][$this->col]['maxs0'])) {
							$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['decimal_align'][$this->col]['maxs0'] = $s0;
						}
						else {
							$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['decimal_align'][$this->col]['maxs0'] = max($s0, $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['decimal_align'][$this->col]['maxs0']);
						}
						if (!isset($this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['decimal_align'][$this->col]['maxs1'])) {
							$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['decimal_align'][$this->col]['maxs1'] = $s1;
						}
						else {
							$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['decimal_align'][$this->col]['maxs1'] = max($s1, $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['decimal_align'][$this->col]['maxs1']);
						}
					}

					if ($this->tableLevel==1 && $this->useGraphs) { 
						$this->graphs[$this->currentGraphId]['data'][$this->row][$this->col] = $e;
					}
					$this->nestedtablejustfinished = false;
					$this->linebreakjustfinished=false;
				   }
				}
/*-- END TABLES --*/
			}
			// ALL ELSE
			else {
    				if ($this->ignorefollowingspaces and !$this->ispre) { $e = ltrim($e); }
				if ($e || $e==='0') $this->_saveTextBuffer($e, $this->HREF);
			}
		}


		else { // TAG **
		   
		   if($e[0]=='/') {
/*-- PROGRESS-BAR --*/
			if ($this->progressBar) { 	// 10% increments
				if (intval($i*10/$cnt) != $pbc) { $pbc = intval($i*10/$cnt); $this->UpdateProgressBar(1,$pbc*10,$tag); }
			}
/*-- END PROGRESS-BAR --*/


		    // Check for tags where HTML specifies optional end tags,
    		    // and/or does not allow nesting e.g. P inside P, or 
		    $endtag = trim(strtoupper(substr($e,1)));	// mPDF 5.4.20
		    if($this->blk[$this->blklvl]['hide']) { 
			if (in_array($endtag, $this->outerblocktags) || in_array($endtag, $this->innerblocktags)) { 
				unset($this->blk[$this->blklvl]);
				$this->blklvl--; 
			}
			continue; 
		    }

/*-- CSS-POSITION --*/
		    if ($this->inFixedPosBlock) { 
			if (in_array($endtag, $this->outerblocktags) || in_array($endtag, $this->innerblocktags)) { $this->fixedPosBlockDepth--; }
			if ($this->fixedPosBlockDepth == 0) { 
				$this->fixedPosBlockSave[] = array($this->fixedPosBlock, $this->fixedPosBlockBBox, $this->page);
				$this->fixedPosBlock = '';
				$this->inFixedPosBlock = false;
				continue; 
			}
			$this->fixedPosBlock .= '<'.$e.'>'; 
			continue; 
		    }
/*-- END CSS-POSITION --*/
		    if ($this->allow_html_optional_endtags && !$parseonly) {
			if (($endtag == 'DIV' || $endtag =='FORM' || $endtag =='CENTER') && $this->lastoptionaltag == 'P') { $this->CloseTag($this->lastoptionaltag ); }
			if ($this->lastoptionaltag == 'LI' && $endtag == 'OL') { $this->CloseTag($this->lastoptionaltag ); }
			if ($this->lastoptionaltag == 'LI' && $endtag == 'UL') { $this->CloseTag($this->lastoptionaltag ); }
			if ($this->lastoptionaltag == 'DD' && $endtag == 'DL') { $this->CloseTag($this->lastoptionaltag ); }
			if ($this->lastoptionaltag == 'DT' && $endtag == 'DL') { $this->CloseTag($this->lastoptionaltag ); }
			if ($this->lastoptionaltag == 'OPTION' && $endtag == 'SELECT') { $this->CloseTag($this->lastoptionaltag ); }
/*-- TABLES --*/
			if ($endtag == 'TABLE') {
				if ($this->lastoptionaltag == 'THEAD' || $this->lastoptionaltag == 'TBODY' || $this->lastoptionaltag == 'TFOOT') { 
					$this->CloseTag($this->lastoptionaltag);
				}
				if ($this->lastoptionaltag == 'TR') { $this->CloseTag('TR'); }
				if ($this->lastoptionaltag == 'TD' || $this->lastoptionaltag == 'TH') { $this->CloseTag($this->lastoptionaltag ); $this->CloseTag('TR'); }
			}
			if ($endtag == 'THEAD' || $endtag == 'TBODY' || $endtag == 'TFOOT') { 
				if ($this->lastoptionaltag == 'TR') { $this->CloseTag('TR'); }
				if ($this->lastoptionaltag == 'TD' || $this->lastoptionaltag == 'TH') { $this->CloseTag($this->lastoptionaltag ); $this->CloseTag('TR'); }
			}
			if ($endtag == 'TR') {
				if ($this->lastoptionaltag == 'TD' || $this->lastoptionaltag == 'TH') { $this->CloseTag($this->lastoptionaltag ); }
			}
/*-- END TABLES --*/
		    }
		    $this->CloseTag($endtag); 
		   }

		   else {	// OPENING TAG
			if($this->blk[$this->blklvl]['hide']) { 
				if (strpos($e,' ')) { $te = strtoupper(substr($e,0,strpos($e,' '))); }
				else { $te = strtoupper($e); } 
				if (in_array($te, $this->outerblocktags) || in_array($te, $this->innerblocktags)) { 
					$this->blklvl++;
 					$this->blk[$this->blklvl]['hide']=true;
				}
				continue; 
			}

/*-- CSS-POSITION --*/
			if ($this->inFixedPosBlock) { 
				if (strpos($e,' ')) { $te = strtoupper(substr($e,0,strpos($e,' '))); }
				else { $te = strtoupper($e); } 
				$this->fixedPosBlock .= '<'.$e.'>'; 
				if (in_array($te, $this->outerblocktags) || in_array($te, $this->innerblocktags)) { $this->fixedPosBlockDepth++; }
				continue; 
			}
/*-- END CSS-POSITION --*/
			$regexp = '|=\'(.*?)\'|s'; // eliminate single quotes, if any
      		$e = preg_replace($regexp,"=\"\$1\"",$e);
			// changes anykey=anyvalue to anykey="anyvalue" (only do this inside [some] tags)
			if (substr($e,0,10)!='pageheader' && substr($e,0,10)!='pagefooter' && substr($e,0,12)!='tocpagebreak') {	// mPDF 5.6.69
				$regexp = '| (\\w+?)=([^\\s>"]+)|si'; 
	      		$e = preg_replace($regexp," \$1=\"\$2\"",$e);
			}

      		$e = preg_replace('/ (\\S+?)\s*=\s*"/i', " \\1=\"", $e);

      		//Fix path values, if needed
			$orig_srcpath = '';
			if ((stristr($e,"href=") !== false) or (stristr($e,"src=") !== false) ) {
				$regexp = '/ (href|src)\s*=\s*"(.*?)"/i';
				preg_match($regexp,$e,$auxiliararray);
				if (isset($auxiliararray[2])) { $path = $auxiliararray[2]; }
				else { $path = ''; }
				if (trim($path) != '' && !(stristr($e,"src=") !== false && substr($path,0,4)=='var:')) { 
					$orig_srcpath = $path;
					$this->GetFullPath($path); 
					$regexp = '/ (href|src)="(.*?)"/i';
					$e = preg_replace($regexp,' \\1="'.$path.'"',$e);
				}
			}//END of Fix path values


			//Extract attributes
			$contents=array();
			$contents1=array();	// mPDF 5.5.17
			$contents2=array();
			// Changed to allow style="background: url('bg.jpg')"
			// mPDF 5.5.17  Changed to improve performance; maximum length of \S (attribute) = 16
			// mPDF 5.6.30  Increase allowed attribute name to 32 - cutting off "toc-even-header-name" etc.
			preg_match_all('/\\S{1,32}=["][^"]*["]/',$e,$contents1);
			preg_match_all('/\\S{1,32}=[\'][^\']*[\']/i',$e,$contents2);

			$contents = array_merge($contents1, $contents2);
			preg_match('/\\S+/',$e,$a2);
			$tag=strtoupper($a2[0]);
			$attr=array();
			if ($orig_srcpath) { $attr['ORIG_SRC'] = $orig_srcpath; }
			if (!empty($contents)) {
				foreach($contents[0] as $v) {
					// Changed to allow style="background: url('bg.jpg')"
 					if(preg_match('/^([^=]*)=["]?([^"]*)["]?$/',$v,$a3) || preg_match('/^([^=]*)=[\']?([^\']*)[\']?$/',$v,$a3)) {
 						if (strtoupper($a3[1])=='ID' || strtoupper($a3[1])=='CLASS') {	// 4.2.013 Omits STYLE
   							$attr[strtoupper($a3[1])]=trim(strtoupper($a3[2]));
						}
						// includes header-style-right etc. used for <pageheader>
 						else if (preg_match('/^(HEADER|FOOTER)-STYLE/i',$a3[1])) {
   							$attr[strtoupper($a3[1])]=trim(strtoupper($a3[2]));
						}
						else {
    							$attr[strtoupper($a3[1])]=trim($a3[2]);
						}
     					}
  				}
			}
			$this->OpenTag($tag,$attr);
/*-- CSS-POSITION --*/
			if ($this->inFixedPosBlock) { 
				$this->fixedPosBlockBBox = array($tag,$attr, $this->x, $this->y); 
				$this->fixedPosBlock = ''; 
				$this->fixedPosBlockDepth = 1; 
			}
/*-- END CSS-POSITION --*/
		      // mPDF 5.5.09
		      if (preg_match('/\/$/',$e)) { $this->closeTag($tag); }

		   }

		} // end TAG
	} //end of	foreach($a as $i=>$e)

	if ($close) {

		// Close any open block tags
		for ($b= $this->blklvl;$b>0;$b--) { $this->CloseTag($this->blk[$b]['tag']); }

		// Output any text left in buffer
		if (count($this->textbuffer) && !$parseonly) { $this->printbuffer($this->textbuffer); }
		if (!$parseonly) $this->textbuffer=array();

/*-- CSS-FLOAT --*/
		// If ended with a float, need to move to end page
		$currpos = $this->page*1000 + $this->y;
		if (isset($this->blk[$this->blklvl]['float_endpos']) && $this->blk[$this->blklvl]['float_endpos'] > $currpos) {
			$old_page = $this->page;
			$new_page = intval($this->blk[$this->blklvl]['float_endpos'] /1000);
			if ($old_page != $new_page) {
				$s = $this->PrintPageBackgrounds();
				// Writes after the marker so not overwritten later by page background etc.
				$this->pages[$this->page] = preg_replace('/(___BACKGROUND___PATTERNS'.date('jY').')/', '\\1'."\n".$s."\n", $this->pages[$this->page]);
				$this->pageBackgrounds = array();
				$this->page = $new_page;
				$this->ResetMargins();
				$this->Reset();
				$this->pageoutput[$this->page] = array();
			}
			$this->y = (($this->blk[$this->blklvl]['float_endpos'] *1000) % 1000000)/1000;	// mod changes operands to integers before processing
		}
/*-- END CSS-FLOAT --*/

/*-- CSS-IMAGE-FLOAT --*/
		$this->printfloatbuffer();
/*-- END CSS-IMAGE-FLOAT --*/

		//Create Internal Links, if needed
		if (!empty($this->internallink) ) {
			foreach($this->internallink as $k=>$v) {
				if (strpos($k,"#") !== false ) { continue; } //ignore
				$ypos = $v['Y'];
				$pagenum = $v['PAGE'];
				$sharp = "#";
				while (array_key_exists($sharp.$k,$this->internallink)) {
					$internallink = $this->internallink[$sharp.$k];
					$this->SetLink($internallink,$ypos,$pagenum);
					$sharp .= "#";
				}
			}
		}

		$this->linemaxfontsize = '';
		$this->lineheight_correction = $this->default_lineheight_correction;

		$this->bufferoutput = false; 

/*-- CSS-POSITION --*/
		if (count($this->fixedPosBlockSave) && $sub != 4) {
		  foreach($this->fixedPosBlockSave AS $fpbs) {
			$old_page = $this->page;
			$this->page = $fpbs[2];
			$this->WriteFixedPosHTML($fpbs[0], 0, 0, 100, 100,'auto', $fpbs[1]);  // 0,0,10,10 are overwritten by bbox
			$this->page = $old_page;
		  }
		}
/*-- END CSS-POSITION --*/

	}
}

/*-- CSS-POSITION --*/

function WriteFixedPosHTML($html='',$x, $y, $w, $h, $overflow='visible', $bounding=array()) {
	// $overflow can be 'hidden', 'visible' or 'auto' - 'auto' causes autofit to size
	// Annotations disabled - enabled in mPDF 5.0
	// Links do work
	// Will always go on current page (or start Page 1 if required)
	// Probably INCOMPATIBLE WITH keep with table, columns etc.
	// Called externally or interally via <div style="position: [fixed|absolute]">
	// When used internally, $x $y $w $h and $overflow are all overridden by $bounding

	$overflow = strtolower($overflow);
	if($this->state==0) { 
		$this->AddPage($this->CurOrientation);
	}
	$save_y = $this->y;
	$save_x = $this->x;
	$this->fullImageHeight = $this->h;
	$save_cols = false;
/*-- COLUMNS --*/
	if ($this->ColActive) {
		$save_cols = true;
		$save_nbcol = $this->NbCol;	// other values of gap and vAlign will not change by setting Columns off
		$this->SetColumns(0);
	}
/*-- END COLUMNS --*/
	$save_annots = $this->title2annots;	// *ANNOTATIONS*
	$this->writingHTMLheader = true;	// a FIX to stop pagebreaks etc.
	$this->writingHTMLfooter = true;
	$this->InFooter = true;	// suppresses autopagebreaks
	$save_bgs = $this->pageBackgrounds;
	$checkinnerhtml = preg_replace('/\s/','',$html);

	if ($w > $this->w) { $x = 0; $w = $this->w; }
	if ($h > $this->h) { $y = 0; $h = $this->h; }
	if ($x > $this->w) { $x = $this->w - $w; }
	if ($y > $this->h) { $y = $this->h - $h; }

	if (!empty($bounding)) {
		// $cont_ containing block = full physical page (position: absolute) or page inside margins (position: fixed)
		// $bbox_ Bounding box is the <div> which is positioned absolutely/fixed 
		// top/left/right/bottom/width/height/background*/border*/padding*/margin* are taken from bounding
		// font*[family/size/style/weight]/line-height/text*[align/decoration/transform/indent]/color are transferred to $inner
		// as an enclosing <div> (after having checked ID/CLASS)
		// $x, $y, $w, $h are inside of $bbox_ = containing box for $inner_
		// $inner_ InnerHTML is the contents of that block to be output 
		$tag = $bounding[0];
		$attr = $bounding[1];
		$orig_x0 = $bounding[2];
		$orig_y0 = $bounding[3];

		// As in WriteHTML() initialising
		$this->blklvl = 0;
		$this->lastblocklevelchange = 0;
		$this->blk = array();
		$this->initialiseBlock($this->blk[0]);

		$this->blk[0]['width'] =& $this->pgwidth;
		$this->blk[0]['inner_width'] =& $this->pgwidth;

		$this->blk[0]['blockContext'] = $this->blockContext;

		$properties = $this->cssmgr->MergeCSS('BLOCK','BODY','');
		$this->setCSS($properties,'','BODY'); 
		$this->blklvl = 1;
		$this->initialiseBlock($this->blk[1]);
		$this->blk[1]['tag'] = $tag;
		$this->blk[1]['attr'] = $attr;
		$this->Reset();
		$p = $this->cssmgr->MergeCSS('BLOCK',$tag,$attr);
		if (isset($p['ROTATE']) && ($p['ROTATE']==90 || $p['ROTATE']==-90)) { $rotate = $p['ROTATE']; }
		else { $rotate = 0; }
		if (isset($p['OVERFLOW'])) { $overflow = strtolower($p['OVERFLOW']); }
		if (strtolower($p['POSITION']) == 'fixed') {
			$cont_w = $this->pgwidth;	// $this->blk[0]['inner_width'];
			$cont_h = $this->h - $this->tMargin - $this->bMargin;
			$cont_x = $this->lMargin;
			$cont_y = $this->tMargin;
		}
		else {
			$cont_w = $this->w;	// ABSOLUTE;
			$cont_h = $this->h;
			$cont_x = 0;
			$cont_y = 0;
		}

		// Pass on in-line properties to the innerhtml
		$css = '';
		if (isset($p['TEXT-ALIGN'])) { $css .= 'text-align: '.strtolower($p['TEXT-ALIGN']).'; '; }
		if (isset($p['TEXT-TRANSFORM'])) { $css .= 'text-transform: '.strtolower($p['TEXT-TRANSFORM']).'; '; }
		if (isset($p['TEXT-INDENT'])) { $css .= 'text-indent: '.strtolower($p['TEXT-INDENT']).'; '; }
		if (isset($p['TEXT-DECORATION'])) { $css .= 'text-decoration: '.strtolower($p['TEXT-DECORATION']).'; '; }
		if (isset($p['FONT-FAMILY'])) { $css .= 'font-family: '.strtolower($p['FONT-FAMILY']).'; '; }
		if (isset($p['FONT-STYLE'])) { $css .= 'font-style: '.strtolower($p['FONT-STYLE']).'; '; }
		if (isset($p['FONT-WEIGHT'])) { $css .= 'font-weight: '.strtolower($p['FONT-WEIGHT']).'; '; }
		if (isset($p['FONT-SIZE'])) { $css .= 'font-size: '.strtolower($p['FONT-SIZE']).'; '; }
		if (isset($p['LINE-HEIGHT'])) { $css .= 'line-height: '.strtolower($p['LINE-HEIGHT']).'; '; }
		if (isset($p['TEXT-SHADOW'])) { $css .= 'text-shadow: '.strtolower($p['TEXT-SHADOW']).'; '; }
		if (isset($p['LETTER-SPACING'])) { $css .= 'letter-spacing: '.strtolower($p['LETTER-SPACING']).'; '; }
		if (isset($p['FONT-VARIANT'])) { $css .= 'font-variant: '.strtolower($p['FONT-VARIANT']).'; '; }
		if (isset($p['COLOR'])) { $css .= 'color: '.strtolower($p['COLOR']).'; '; }
		if (isset($p['Z-INDEX'])) { $css .= 'z-index: '.$p['Z-INDEX'].'; '; }	// mPDF 5.6.01
		if ($css) {
			$html = '<div style="'.$css.'">'.$html.'</div>';
		}
		// Copy over (only) the properties to set for border and background
		$pb = array();
		$pb['MARGIN-TOP'] = $p['MARGIN-TOP']; 
		$pb['MARGIN-RIGHT'] = $p['MARGIN-RIGHT']; 
		$pb['MARGIN-BOTTOM'] = $p['MARGIN-BOTTOM']; 
		$pb['MARGIN-LEFT'] = $p['MARGIN-LEFT']; 
		$pb['PADDING-TOP'] = $p['PADDING-TOP']; 
		$pb['PADDING-RIGHT'] = $p['PADDING-RIGHT']; 
		$pb['PADDING-BOTTOM'] = $p['PADDING-BOTTOM']; 
		$pb['PADDING-LEFT'] = $p['PADDING-LEFT']; 
		$pb['BORDER-TOP'] = $p['BORDER-TOP']; 
		$pb['BORDER-RIGHT'] = $p['BORDER-RIGHT']; 
		$pb['BORDER-BOTTOM'] = $p['BORDER-BOTTOM']; 
		$pb['BORDER-LEFT'] = $p['BORDER-LEFT']; 
		$pb['BORDER-TOP-LEFT-RADIUS-H'] = $p['BORDER-TOP-LEFT-RADIUS-H'];
		$pb['BORDER-TOP-LEFT-RADIUS-V'] = $p['BORDER-TOP-LEFT-RADIUS-V'];
		$pb['BORDER-TOP-RIGHT-RADIUS-H'] = $p['BORDER-TOP-RIGHT-RADIUS-H'];
		$pb['BORDER-TOP-RIGHT-RADIUS-V'] = $p['BORDER-TOP-RIGHT-RADIUS-V'];
		$pb['BORDER-BOTTOM-LEFT-RADIUS-H'] = $p['BORDER-BOTTOM-LEFT-RADIUS-H'];
		$pb['BORDER-BOTTOM-LEFT-RADIUS-V'] = $p['BORDER-BOTTOM-LEFT-RADIUS-V'];
		$pb['BORDER-BOTTOM-RIGHT-RADIUS-H'] = $p['BORDER-BOTTOM-RIGHT-RADIUS-H'];
		$pb['BORDER-BOTTOM-RIGHT-RADIUS-V'] = $p['BORDER-BOTTOM-RIGHT-RADIUS-V'];
		if (isset($p['BACKGROUND-COLOR'])) { $pb['BACKGROUND-COLOR'] = $p['BACKGROUND-COLOR']; }
		if (isset($p['BOX-SHADOW'])) { $pb['BOX-SHADOW'] = $p['BOX-SHADOW']; }
/*-- BACKGROUNDS --*/
		if (isset($p['BACKGROUND-IMAGE'])) { $pb['BACKGROUND-IMAGE'] = $p['BACKGROUND-IMAGE']; }
		if (isset($p['BACKGROUND-IMAGE-RESIZE'])) { $pb['BACKGROUND-IMAGE-RESIZE'] = $p['BACKGROUND-IMAGE-RESIZE']; }
		if (isset($p['BACKGROUND-IMAGE-OPACITY'])) { $pb['BACKGROUND-IMAGE-OPACITY'] = $p['BACKGROUND-IMAGE-OPACITY']; }
		if (isset($p['BACKGROUND-REPEAT'])) { $pb['BACKGROUND-REPEAT'] = $p['BACKGROUND-REPEAT']; }
		if (isset($p['BACKGROUND-POSITION'])) { $pb['BACKGROUND-POSITION'] = $p['BACKGROUND-POSITION']; }
		if (isset($p['BACKGROUND-GRADIENT'])) { $pb['BACKGROUND-GRADIENT'] = $p['BACKGROUND-GRADIENT']; }
		if (isset($p['BACKGROUND-SIZE'])) { $pb['BACKGROUND-SIZE'] = $p['BACKGROUND-SIZE']; }	// mPDF 5.6.12
		if (isset($p['BACKGROUND-ORIGIN'])) { $pb['BACKGROUND-ORIGIN'] = $p['BACKGROUND-ORIGIN']; }	// mPDF 5.6.12
		if (isset($p['BACKGROUND-CLIP'])) { $pb['BACKGROUND-CLIP'] = $p['BACKGROUND-CLIP']; }	// mPDF 5.6.12

/*-- END BACKGROUNDS --*/

		$this->setCSS($pb,'BLOCK',$tag);

		//================================================================
		$bbox_br = $this->blk[1]['border_right']['w'];
		$bbox_bl = $this->blk[1]['border_left']['w'];
		$bbox_bt = $this->blk[1]['border_top']['w'];
		$bbox_bb = $this->blk[1]['border_bottom']['w'];
		$bbox_pr = $this->blk[1]['padding_right'];
		$bbox_pl = $this->blk[1]['padding_left'];
		$bbox_pt = $this->blk[1]['padding_top'];
		$bbox_pb = $this->blk[1]['padding_bottom'];
		$bbox_mr = $this->blk[1]['margin_right'];
		if (strtolower($p['MARGIN-RIGHT'])=='auto') { $bbox_mr = 'auto'; }
		$bbox_ml = $this->blk[1]['margin_left'];
		if (strtolower($p['MARGIN-LEFT'])=='auto') { $bbox_ml = 'auto'; }
		$bbox_mt = $this->blk[1]['margin_top'];
		if (strtolower($p['MARGIN-TOP'])=='auto') { $bbox_mt = 'auto'; }
		$bbox_mb = $this->blk[1]['margin_bottom'];
 		if (strtolower($p['MARGIN-BOTTOM'])=='auto') { $bbox_mb = 'auto'; }
		if (isset($p['LEFT']) && strtolower($p['LEFT'])!='auto') { $bbox_left = $this->ConvertSize($p['LEFT'], $cont_w, $this->FontSize,false); }
		else { $bbox_left = 'auto'; }
 		if (isset($p['TOP']) && strtolower($p['TOP'])!='auto') { $bbox_top = $this->ConvertSize($p['TOP'], $cont_h, $this->FontSize,false); }
		else { $bbox_top = 'auto'; }
 		if (isset($p['RIGHT']) && strtolower($p['RIGHT'])!='auto') { $bbox_right = $this->ConvertSize($p['RIGHT'], $cont_w, $this->FontSize,false); }
		else { $bbox_right = 'auto'; }
 		if (isset($p['BOTTOM']) && strtolower($p['BOTTOM'])!='auto') { $bbox_bottom = $this->ConvertSize($p['BOTTOM'], $cont_h, $this->FontSize,false); }
		else { $bbox_bottom = 'auto'; }
 		if (isset($p['WIDTH']) && strtolower($p['WIDTH'])!='auto') { $inner_w = $this->ConvertSize($p['WIDTH'], $cont_w, $this->FontSize,false); }
		else { $inner_w = 'auto'; }
 		if (isset($p['HEIGHT']) && strtolower($p['HEIGHT'])!='auto') { $inner_h = $this->ConvertSize($p['HEIGHT'], $cont_h, $this->FontSize,false); }
		else { $inner_h = 'auto'; }

		// If bottom or right pos are set and not left / top - save this to adjust rotated block later
		if ($rotate) {
			if ($bbox_left === 'auto' && $bbox_right !== 'auto') { $rot_rpos = $bbox_right; }
			else { $rot_rpos = false; }
			if ($bbox_top === 'auto' && $bbox_bottom !== 'auto') { $rot_bpos = $bbox_bottom; }
			else { $rot_bpos = false; }
		}

		//================================================================
		if ($checkinnerhtml=='' && $inner_h==='auto') { $inner_h = 0.0001; }
		if ($checkinnerhtml=='' && $inner_w==='auto') { $inner_w = 2*$this->GetCharWidth('W',false); }
		//================================================================
		// Algorithm from CSS2.1  See http://www.w3.org/TR/CSS21/visudet.html#abs-non-replaced-height
		// mPD 5.3.14
		// Special case (not CSS) if all not specified, centre vertically on page
		if ($bbox_top==='auto' && $inner_h==='auto' && $bbox_bottom==='auto' && $bbox_mt==='auto' && $bbox_mb==='auto') {
			$bbox_top_orig = $bbox_top; 
			if ($bbox_mt==='auto') { $bbox_mt = 0; }
			if ($bbox_mb==='auto') { $bbox_mb = 0; }
			$bbox_top = $orig_y0 - $bbox_mt - $cont_y;
			// solve for $bbox_bottom when content_h known - $inner_h=='auto' && $bbox_bottom=='auto'
		}
		// mPD 5.3.14
		else if ($bbox_top==='auto' && $inner_h==='auto' && $bbox_bottom==='auto') {
			$bbox_top_orig = $bbox_top = $orig_y0 - $cont_y; 
			if ($bbox_mt==='auto') { $bbox_mt = 0; }
			if ($bbox_mb==='auto') { $bbox_mb = 0; }
			// solve for $bbox_bottom when content_h known - $inner_h=='auto' && $bbox_bottom=='auto'
		}
		else if ($bbox_top!=='auto' && $inner_h!=='auto' && $bbox_bottom!=='auto') {
			if ($bbox_mt==='auto' && $bbox_mb==='auto') {
				$x = $cont_h - $bbox_top - $bbox_bt - $bbox_pt - $inner_h - $bbox_pb - $bbox_bb - $bbox_bottom;
				$bbox_mt = $bbox_mb = ($x/2);
			}
			else if ($bbox_mt==='auto') {
				$bbox_mt = $cont_h - $bbox_top - $bbox_bt - $bbox_pt - $inner_h - $bbox_pb - $bbox_bb - $bbox_mb - $bbox_bottom;
			}
			else if ($bbox_mb==='auto') {
				$bbox_mb = $cont_h - $bbox_top - $bbox_mt - $bbox_bt - $bbox_pt - $inner_h - $bbox_pb - $bbox_bb - $bbox_bottom;
			}
			else {
				$bbox_bottom = $cont_h - $bbox_top - $bbox_mt - $bbox_bt - $bbox_pt - $inner_h - $bbox_pb - $bbox_bb - $bbox_mt;
			}
		}
		else {
		  if ($bbox_mt==='auto') { $bbox_mt = 0; }
		  if ($bbox_mb==='auto') { $bbox_mb = 0; }
		  if ($bbox_top==='auto' && $inner_h==='auto' && $bbox_bottom!=='auto') {
			// solve for $bbox_top when content_h known - $inner_h=='auto' && $bbox_top =='auto'
		  }
		  else if ($bbox_top==='auto' && $bbox_bottom==='auto' && $inner_h!=='auto') {
			$bbox_top = $orig_y0 - $bbox_mt - $cont_y;
			$bbox_bottom = $cont_h - $bbox_top - $bbox_mt - $bbox_bt - $bbox_pt - $inner_h - $bbox_pb - $bbox_bb - $bbox_mt;
		  }
		  else if ($inner_h==='auto' && $bbox_bottom==='auto' && $bbox_top!=='auto') {
			// solve for $bbox_bottom when content_h known - $inner_h=='auto' && $bbox_bottom=='auto'
		  }
		  else if ($bbox_top==='auto' && $inner_h!=='auto' && $bbox_bottom!=='auto') {
			$bbox_top = $cont_h - $bbox_mt - $bbox_bt - $bbox_pt - $inner_h - $bbox_pb - $bbox_bb - $bbox_mt - $bbox_bottom;
		  }
		  else if ($inner_h==='auto' && $bbox_top!=='auto' && $bbox_bottom!=='auto') {
			$inner_h = $cont_h - $bbox_top - $bbox_mt - $bbox_bt - $bbox_pt - $bbox_pb - $bbox_bb - $bbox_mt - $bbox_bottom;
		  }
		  else if ($bbox_bottom==='auto' && $bbox_top!=='auto' && $inner_h!=='auto') {
			$bbox_bottom = $cont_h - $bbox_top - $bbox_mt - $bbox_bt - $bbox_pt - $inner_h - $bbox_pb - $bbox_bb - $bbox_mt;
		  }
		}

		// THEN DO SAME FOR WIDTH
		// http://www.w3.org/TR/CSS21/visudet.html#abs-non-replaced-width
		if ($bbox_left==='auto' && $inner_w==='auto' && $bbox_right==='auto') {
			if ($bbox_ml==='auto') { $bbox_ml = 0; }
			if ($bbox_mr==='auto') { $bbox_mr = 0; }
			// IF containing element RTL, should set $bbox_right
			$bbox_left = $orig_x0 - $bbox_ml - $cont_x;
			// solve for $bbox_right when content_w known - $inner_w=='auto' && $bbox_right=='auto'
		}
		else if ($bbox_left!=='auto' && $inner_w!=='auto' && $bbox_right!=='auto') {
			if ($bbox_ml==='auto' && $bbox_mr==='auto') {
				$x = $cont_w - $bbox_left - $bbox_bl - $bbox_pl - $inner_w - $bbox_pr - $bbox_br - $bbox_right;
				$bbox_ml = $bbox_mr = ($x/2);
			}
			else if ($bbox_ml==='auto') {
				$bbox_ml = $cont_w - $bbox_left - $bbox_bl - $bbox_pl - $inner_w - $bbox_pr - $bbox_br - $bbox_mr - $bbox_right;
			}
			else if ($bbox_mr==='auto') {
				$bbox_mr = $cont_w - $bbox_left - $bbox_ml - $bbox_bl - $bbox_pl - $inner_w - $bbox_pr - $bbox_br - $bbox_right;
			}
			else {
				$bbox_right = $cont_w - $bbox_left - $bbox_ml - $bbox_bl - $bbox_pl - $inner_w - $bbox_pr - $bbox_br - $bbox_ml;
			}
		}
		else {
		  if ($bbox_ml==='auto') { $bbox_ml = 0; }
		  if ($bbox_mr==='auto') { $bbox_mr = 0; }
		  if ($bbox_left==='auto' && $inner_w==='auto' && $bbox_right!=='auto') {
			// solve for $bbox_left when content_w known - $inner_w=='auto' && $bbox_left =='auto'
		  }
		  else if ($bbox_left==='auto' && $bbox_right==='auto' && $inner_w!=='auto') {
			// IF containing element RTL, should set $bbox_right
			$bbox_left = $orig_x0 - $bbox_ml - $cont_x;
			$bbox_right = $cont_w - $bbox_left - $bbox_ml - $bbox_bl - $bbox_pl - $inner_w - $bbox_pr - $bbox_br - $bbox_ml;
		  }
		  else if ($inner_w==='auto' && $bbox_right==='auto' && $bbox_left!=='auto') {
			// solve for $bbox_right when content_w known - $inner_w=='auto' && $bbox_right=='auto'
		  }
		  else if ($bbox_left==='auto' && $inner_w!=='auto' && $bbox_right!=='auto') {
			$bbox_left = $cont_w - $bbox_ml - $bbox_bl - $bbox_pl - $inner_w - $bbox_pr - $bbox_br - $bbox_ml - $bbox_right;
		  }
		  else if ($inner_w==='auto' && $bbox_left!=='auto' && $bbox_right!=='auto') {
			$inner_w = $cont_w - $bbox_left - $bbox_ml - $bbox_bl - $bbox_pl - $bbox_pr - $bbox_br - $bbox_ml - $bbox_right;
		  }
		  else if ($bbox_right==='auto' && $bbox_left!=='auto' && $inner_w!=='auto') {
			$bbox_right = $cont_w - $bbox_left - $bbox_ml - $bbox_bl - $bbox_pl - $inner_w - $bbox_pr - $bbox_br - $bbox_ml;
		  }
		}

		//================================================================
		//================================================================
/*-- BACKGROUNDS --*/
		if (isset($pb['BACKGROUND-IMAGE']) && $pb['BACKGROUND-IMAGE']) { 
			$ret = $this->SetBackground($pb, $this->blk[1]['inner_width']);
			if ($ret) { $this->blk[1]['background-image'] = $ret; }
		}
/*-- END BACKGROUNDS --*/

		//================================================================
		$y = $cont_y + $bbox_top + $bbox_mt + $bbox_bt + $bbox_pt;
		$h = $cont_h - $bbox_top - $bbox_mt - $bbox_bt - $bbox_pt - $bbox_pb - $bbox_bb - $bbox_mb - $bbox_bottom;
		$x = $cont_x + $bbox_left + $bbox_ml + $bbox_bl + $bbox_pl;
		$w = $cont_w - $bbox_left - $bbox_ml - $bbox_bl - $bbox_pl - $bbox_pr - $bbox_br - $bbox_mr - $bbox_right;
		// Set (temporary) values for x y w h to do first paint, if values are auto
		if ($inner_h==='auto' && $bbox_top==='auto') {
			$y = $cont_y + $bbox_mt + $bbox_bt + $bbox_pt;
			$h = $cont_h - ($bbox_bottom + $bbox_mt + $bbox_mb + $bbox_bt + $bbox_bb + $bbox_pt + $bbox_pb);
		}
		else if ($inner_h==='auto' && $bbox_bottom==='auto') {
			$y = $cont_y + $bbox_top + $bbox_mt + $bbox_bt + $bbox_pt;
			$h = $cont_h - ($bbox_top + $bbox_mt + $bbox_mb + $bbox_bt + $bbox_bb + $bbox_pt + $bbox_pb);
		}
		if ($inner_w==='auto' && $bbox_left==='auto') {
			$x = $cont_x + $bbox_ml + $bbox_bl + $bbox_pl;
			$w = $cont_w - ($bbox_right + $bbox_ml + $bbox_mr + $bbox_bl + $bbox_br + $bbox_pl + $bbox_pr);
		}
		else if ($inner_w==='auto' && $bbox_right==='auto') {
			$x = $cont_x + $bbox_left + $bbox_ml + $bbox_bl + $bbox_pl;
			$w = $cont_w - ($bbox_left + $bbox_ml + $bbox_mr + $bbox_bl + $bbox_br + $bbox_pl + $bbox_pr);
		}
		$bbox_y = $cont_y + $bbox_top + $bbox_mt;
		$bbox_x = $cont_x + $bbox_left + $bbox_ml;
		$saved_block1 = $this->blk[1];
		unset($p);
		unset($pb);
		//================================================================
		if ($inner_w==='auto') { // do a first write
			$this->lMargin=$x;
			$this->rMargin=$this->w - $w - $x;
			// SET POSITION & FONT VALUES
			$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
			$this->pageoutput[$this->page]=array();
			$this->x = $x;
			$this->y = $y;
			$this->HTMLheaderPageLinks = array();
			$this->HTMLheaderPageAnnots = array();
			$this->HTMLheaderPageForms = array();
			$this->pageBackgrounds = array();
			$this->maxPosR = 0;
			$this->maxPosL = $this->w;	// For RTL
			$this->WriteHTML($html , 4);
			$inner_w = $this->maxPosR - $this->lMargin;
			if ($bbox_right==='auto') {
				$bbox_right = $cont_w - $bbox_left - $bbox_ml - $bbox_bl - $bbox_pl - $inner_w - $bbox_pr - $bbox_br - $bbox_ml;
			}
			else if ($bbox_left==='auto') {
				$bbox_left = $cont_w - $bbox_ml - $bbox_bl - $bbox_pl - $inner_w - $bbox_pr - $bbox_br - $bbox_ml - $bbox_right;
				$bbox_x = $cont_x + $bbox_left + $bbox_ml ;
				$inner_x = $bbox_x + $bbox_bl + $bbox_pl;
				$x = $inner_x;
			}
			$w = $inner_w;
			$bbox_y = $cont_y + $bbox_top + $bbox_mt;
			$bbox_x = $cont_x + $bbox_left + $bbox_ml;
		}

		if ($inner_h==='auto') { // do a first write
			$this->lMargin=$x;
			$this->rMargin=$this->w - $w - $x;
			// SET POSITION & FONT VALUES
			$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
			$this->pageoutput[$this->page]=array();
			$this->x = $x;
			$this->y = $y;
			$this->HTMLheaderPageLinks = array();
			$this->HTMLheaderPageAnnots = array();
			$this->HTMLheaderPageForms = array();
			$this->pageBackgrounds = array();
			$this->WriteHTML($html , 4);
			$inner_h = $this->y - $y;
			if ($overflow!='hidden' && $overflow!='visible') {	// constrained
				if (($this->y + $bbox_pb + $bbox_bb) > ($cont_y + $cont_h)) {
					$adj = ($this->y + $bbox_pb + $bbox_bb) - ($cont_y + $cont_h);
					$inner_h -= $adj;
				}
			}
			if ($bbox_bottom==='auto' && $bbox_top_orig==='auto') {
				$bbox_bottom = $bbox_top = ($cont_h - $bbox_mt - $bbox_bt - $bbox_pt - $inner_h - $bbox_pb - $bbox_bb - $bbox_mb)/2;
				if ($overflow!='hidden' && $overflow!='visible') {	// constrained
					if ($bbox_top < 0) {
						$bbox_top = 0;
						$inner_h = $cont_h - $bbox_top - $bbox_mt - $bbox_bt - $bbox_pt - $bbox_pb - $bbox_bb - $bbox_mb - $bbox_bottom;
					}
				}
				$bbox_y = $cont_y + $bbox_top + $bbox_mt;
				$inner_y = $bbox_y + $bbox_bt + $bbox_pt;
				$y = $inner_y;

			}
			else if ($bbox_bottom==='auto') {
				$bbox_bottom = $cont_h - $bbox_top - $bbox_mt - $bbox_bt - $bbox_pt - $inner_h - $bbox_pb - $bbox_bb - $bbox_mb;
			}
			else if ($bbox_top==='auto') {
				$bbox_top = $cont_h - $bbox_mt - $bbox_bt - $bbox_pt - $inner_h - $bbox_pb - $bbox_bb - $bbox_mb - $bbox_bottom;
				if ($overflow!='hidden' && $overflow!='visible') {	// constrained
					if ($bbox_top < 0) {
						$bbox_top = 0;
						$inner_h = $cont_h - $bbox_top - $bbox_mt - $bbox_bt - $bbox_pt - $bbox_pb - $bbox_bb - $bbox_mb - $bbox_bottom;
					}
				}
				$bbox_y = $cont_y + $bbox_top + $bbox_mt;
				$inner_y = $bbox_y + $bbox_bt + $bbox_pt;
				$y = $inner_y;
			}
			$h = $inner_h;
			$bbox_y = $cont_y + $bbox_top + $bbox_mt;
			$bbox_x = $cont_x + $bbox_left + $bbox_ml;
		}
		$inner_w = $w;
		$inner_h = $h;

	}
	$this->lMargin=$x;
	$this->rMargin=$this->w - $w - $x;
	// SET POSITION & FONT VALUES
	$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
	$this->pageoutput[$this->page]=array();
	$this->x = $x;
	$this->y = $y;
	$this->HTMLheaderPageLinks = array();
	$this->HTMLheaderPageAnnots = array();
	$this->HTMLheaderPageForms = array();
	$this->pageBackgrounds = array();
	$this->WriteHTML($html , 4);	// parameter 4 saves output to $this->headerbuffer
	$actual_h = $this->y - $y;
	$use_w = $w;
	$use_h = $h;
	$ratio = $actual_h / $use_w;

	if ($overflow!='hidden' && $overflow!='visible') {
		$target = $h/$w;
		if (($ratio / $target ) > 1) {
			$nl = CEIL($actual_h / $this->lineheight);
			$l = $use_w * $nl;
			$est_w = sqrt(($l * $this->lineheight) / $target) * 0.8;
			$use_w += ($est_w - $use_w) - ($w/100);
		}
		$bpcstart = ($ratio / $target);
		$bpcctr = 1;
		while(($ratio / $target ) > 1) {

			if ($this->progressBar) { $this->UpdateProgressBar(4,intval(100/($ratio/$target)),('Auto-sizing fixed-position block: '.$bpcctr++)); }	// *PROGRESS-BAR*

			$this->x = $x;
			$this->y = $y;

			if (($ratio / $target) > 1.5 || ($ratio / $target) < 0.6) {
				$use_w += ($w/$this->incrementFPR1);
			}
			else if (($ratio / $target) > 1.2 || ($ratio / $target) < 0.85) {
				$use_w += ($w/$this->incrementFPR2);
			}
			else if (($ratio / $target) > 1.1 || ($ratio / $target) < 0.91) {
				$use_w += ($w/$this->incrementFPR3);
			}
			else {
				$use_w += ($w/$this->incrementFPR4);
			}

			$use_h = $use_w * $target ;
			$this->rMargin=$this->w - $use_w - $x;
			$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
			$this->HTMLheaderPageLinks = array();
			$this->HTMLheaderPageAnnots = array();
			$this->HTMLheaderPageForms = array();
			$this->pageBackgrounds = array();
			$this->WriteHTML($html , 4);	// parameter 4 saves output to $this->headerbuffer
			$actual_h = $this->y - $y;
			$ratio = $actual_h / $use_w;
		}
		if ($this->progressBar) { $this->UpdateProgressBar(4,'100',' '); }	// *PROGRESS-BAR*
	}
	$shrink_f = $w/$use_w;

	//================================================================

	$this->pages[$this->page] .= '___BEFORE_BORDERS___';
	$block_s = $this->PrintPageBackgrounds();	// Save to print later inside clipping path
	$this->pageBackgrounds = array();

	//================================================================

	if ($rotate) {
		$prerotw = $bbox_bl + $bbox_pl + $inner_w + $bbox_pr + $bbox_br;
		$preroth = $bbox_bt + $bbox_pt + $inner_h + $bbox_pb + $bbox_bb;
		$rot_start = " q\n";
		if ($rotate == 90) { 
			if ($rot_rpos !== false) { $adjw = $prerotw; }	// width before rotation
			else { $adjw = $preroth; }	// height before rotation
			if ($rot_bpos !== false) { $adjh = -$prerotw + $preroth; }
			else { $adjh = 0; }
		}
		else { 
			if ($rot_rpos !== false) { $adjw = $prerotw - $preroth; }
			else { $adjw = 0; }
			if ($rot_bpos !== false) { $adjh = $preroth; }	// height before rotation
			else { $adjh = $prerotw; }	// width before rotation
		}
		$rot_start .= $this->transformTranslate($adjw, $adjh, true)."\n"; 
		$rot_start .= $this->transformRotate($rotate, $bbox_x, $bbox_y, true)."\n";
		$rot_end = " Q\n";
	}
	else {
		$rot_start = '';
		$rot_end = '';
	}

	//================================================================
	if (!empty($bounding)) {
		// WHEN HEIGHT // BOTTOM EDGE IS KNOWN and $this->y is set to the bottom
		// Re-instate saved $this->blk[1]
		$this->blk[1] = $saved_block1;

		// These are only needed when painting border/background
		$this->blk[1]['width'] = $bbox_w = $cont_w - $bbox_left - $bbox_ml - $bbox_mr - $bbox_right;
		$this->blk[1]['x0'] = $bbox_x;
		$this->blk[1]['y0'] = $bbox_y;
		$this->blk[1]['startpage'] = $this->page;
		$this->blk[1]['y1'] = $bbox_y + $bbox_bt + $bbox_pt + $inner_h + $bbox_pb + $bbox_bb ;
		$this->_out($rot_start);	// mPDF 5.0
		$this->PaintDivBB('',0,1);	// Prints borders and sets backgrounds in $this->pageBackgrounds 
		$this->_out($rot_end);
	}

	$s = $this->PrintPageBackgrounds();
	$s = $rot_start.$s.$rot_end;
	$this->pages[$this->page] = preg_replace('/___BEFORE_BORDERS___/', "\n".$s."\n", $this->pages[$this->page]);
	$this->pageBackgrounds = array();

	$this->_out($rot_start);

	// Clipping Output
	if ($overflow=='hidden') {
		//Bounding rectangle to clip
		$clip_y1 = $this->y;
		if (!empty($bounding) && ($this->y + $bbox_pb + $bbox_bb) > ($bbox_y + $bbox_bt + $bbox_pt + $inner_h + $bbox_pb + $bbox_bb )) {
			$clip_y1 = ($bbox_y + $bbox_bt + $bbox_pt + $inner_h + $bbox_pb + $bbox_bb ) - ($bbox_pb + $bbox_bb);
		}
		//$op = 'W* n';	// Clipping
		$op = 'W n';	// Clipping alternative mode
		$this->_out("q");
		$ch = $clip_y1 - $y;
		$this->_out(sprintf('%.3F %.3F %.3F %.3F re %s',$x*_MPDFK,($this->h-$y)*_MPDFK,$w*_MPDFK,-$ch*_MPDFK,$op));
		if (!empty($block_s)) {
			$tmp = "q\n".sprintf('%.3F %.3F %.3F %.3F re %s',$x*_MPDFK,($this->h-$y)*_MPDFK,$w*_MPDFK,-$ch*_MPDFK,$op);
			$tmp .= "\n".$block_s."\nQ";
			$block_s = $tmp ;
		}
	}


	if (!empty($block_s)) {
		if ($shrink_f != 1) {	// i.e. autofit has resized the box
			$tmp = "q\n".$this->transformScale(($shrink_f*100),($shrink_f*100), $x, $y, true);
			$tmp .= "\n".$block_s."\nQ";
			$block_s = $tmp ;
		}
		$this->_out($block_s);
	}



	if ($shrink_f != 1) {	// i.e. autofit has resized the box
		$this->StartTransform();
		$this->transformScale(($shrink_f*100),($shrink_f*100), $x, $y);
	}

	$this->_out($this->headerbuffer);

	if ($shrink_f != 1) {	// i.e. autofit has resized the box
		$this->StopTransform();
	}

	if ($overflow=='hidden') {
		//End clipping
		$this->_out("Q");
	}

	$this->_out($rot_end);


	// Page Links
	foreach($this->HTMLheaderPageLinks AS $lk) {
		if ($rotate) {
			$tmp = $lk[2];	// Switch h - w
			$lk[2] = $lk[3];
			$lk[3] = $tmp;

			$lx1 = (($lk[0]/_MPDFK));
			$ly1 = (($this->h-($lk[1]/_MPDFK)));
			if ($rotate == 90) {
				$adjx = -($lx1-$bbox_x) + ($preroth - ($ly1-$bbox_y));
				$adjy = -($ly1-$bbox_y) + ($lx1-$bbox_x);
				$lk[2] = -$lk[2];
			}
			else if ($rotate == -90) {
				$adjx = -($lx1-$bbox_x) + ($ly1-$bbox_y);
				$adjy = -($ly1-$bbox_y) - ($lx1-$bbox_x) + $prerotw;
				$lk[3] = -$lk[3];
			}
			if ($rot_rpos !== false) { $adjx += $prerotw - $preroth; }
			if ($rot_bpos !== false) { $adjy += $preroth - $prerotw; }
			$lx1 += $adjx;
			$ly1 += $adjy;

			$lk[0] = $lx1*_MPDFK;
			$lk[1] = ($this->h-$ly1)*_MPDFK;
		}
		if ($shrink_f != 1) { 	// i.e. autofit has resized the box
			$lx1 = (($lk[0]/_MPDFK)-$x);
			$lx2 = $x + ($lx1 * $shrink_f);
			$lk[0] = $lx2*_MPDFK;
			$ly1 = (($this->h-($lk[1]/_MPDFK))-$y);
			$ly2 = $y + ($ly1 * $shrink_f);
			$lk[1] = ($this->h-$ly2)*_MPDFK;
			$lk[2] *= $shrink_f;	// width
			$lk[3] *= $shrink_f;	// height
		}
		$this->PageLinks[$this->page][]=$lk;
	}

	foreach($this->HTMLheaderPageForms AS $n=>$f) {
		if ($shrink_f != 1) { 	// i.e. autofit has resized the box
			$f['x'] = $x + (($f['x'] -$x) * $shrink_f);
			$f['y'] = $y + (($f['y'] -$y) * $shrink_f);
			$f['w'] *= $shrink_f;
			$f['h'] *= $shrink_f;
			$f['style']['fontsize'] *= $shrink_f;
		}
		$this->form->forms[$f['n']] = $f;
	}
	// Page Annotations
	foreach($this->HTMLheaderPageAnnots AS $lk) {
		if ($rotate) {
			if ($rotate == 90) {
				$adjx = -($lk['x']-$bbox_x) + ($preroth - ($lk['y']-$bbox_y));
				$adjy = -($lk['y']-$bbox_y) + ($lk['x']-$bbox_x);
			}
			else if ($rotate == -90) {
				$adjx = -($lk['x']-$bbox_x) + ($lk['y']-$bbox_y);
				$adjy = -($lk['y']-$bbox_y) - ($lk['x']-$bbox_x) + $prerotw;
			}
			if ($rot_rpos !== false) { $adjx += $prerotw - $preroth; }
			if ($rot_bpos !== false) { $adjy += $preroth - $prerotw; }
			$lk['x'] += $adjx;
			$lk['y'] += $adjy;
		}
		if ($shrink_f != 1) { 	// i.e. autofit has resized the box
			$lk['x'] = $x + (($lk['x']-$x) * $shrink_f);
			$lk['y'] = $y + (($lk['y']-$y) * $shrink_f);
		}
		$this->PageAnnots[$this->page][]=$lk;
	}

	// Restore
	$this->headerbuffer = '';
	$this->HTMLheaderPageLinks = array();
	$this->HTMLheaderPageAnnots = array();
	$this->HTMLheaderPageForms = array();
	$this->pageBackgrounds = $save_bgs;
	$this->writingHTMLheader = false;

	$this->writingHTMLfooter = false;
	$this->fullImageHeight = false;
	$this->ResetMargins();
	$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
	$this->SetXY($save_x,$save_y) ; 
	$this->title2annots = $save_annots;	// *ANNOTATIONS*
	$this->InFooter = false;	// turns back on autopagebreaks
	$this->pageoutput[$this->page]=array();
	$this->pageoutput[$this->page]['Font']='';
/*-- COLUMNS --*/
	if ($save_cols) {
		$this->SetColumns($save_nbcol,$this->colvAlign,$this->ColGap);
	}
/*-- END COLUMNS --*/
}
/*-- END CSS-POSITION --*/



function initialiseBlock(&$blk) {
	$blk['margin_top'] = 0;
	$blk['margin_left'] = 0;
	$blk['margin_bottom'] = 0;
	$blk['margin_right'] = 0;
	$blk['padding_top'] = 0;
	$blk['padding_left'] = 0;
	$blk['padding_bottom'] = 0;
	$blk['padding_right'] = 0;
	$blk['border_top']['w'] = 0;
	$blk['border_left']['w'] = 0;
	$blk['border_bottom']['w'] = 0;
	$blk['border_right']['w'] = 0;
	$blk['hide'] = false; 
	$blk['outer_left_margin'] = 0; 
	$blk['outer_right_margin'] = 0; 
	$blk['cascadeCSS'] = array(); 
	$blk['block-align'] = false; 
	$blk['bgcolor'] = false; 
	$blk['page_break_after_avoid'] = false; 
	$blk['keep_block_together'] = false; 
	$blk['float'] = false; 
	$blk['line_height'] = ''; 
	$blk['margin_collapse'] = false; 
}


function border_details($bd) {
	$prop = preg_split('/\s+/',trim($bd));

	if (isset($this->blk[$this->blklvl]['inner_width'])) { $refw = $this->blk[$this->blklvl]['inner_width']; }
	else if (isset($this->blk[$this->blklvl-1]['inner_width'])) { $refw = $this->blk[$this->blklvl-1]['inner_width']; }
	else { $refw = $this->w; }
	if ( count($prop) == 1 ) { 
		$bsize = $this->ConvertSize($prop[0],$refw,$this->FontSize,false);
		if ($bsize > 0) {
			return array('s' => 1, 'w' => $bsize, 'c' => $this->ConvertColor(0), 'style'=>'solid');
		}
		else { return array('w' => 0, 's' => 0); }
	}

	else if (count($prop) == 2 ) { 
		// 1px solid 
		if (in_array($prop[1],$this->borderstyles) || $prop[1] == 'none' || $prop[1] == 'hidden' ) { $prop[2] = ''; }
		// solid #000000 
		else if (in_array($prop[0],$this->borderstyles) || $prop[0] == 'none' || $prop[0] == 'hidden' ) { $prop[0] = ''; $prop[1] = $prop[0]; $prop[2] = $prop[1]; }
		// 1px #000000 
		else { $prop[1] = ''; $prop[2] = $prop[1]; }
	}
	else if ( count($prop) == 3 ) {
		// Change #000000 1px solid to 1px solid #000000 (proper)
		if (substr($prop[0],0,1) == '#') { $tmp = $prop[0]; $prop[0] = $prop[1]; $prop[1] = $prop[2]; $prop[2] = $tmp; }
		// Change solid #000000 1px to 1px solid #000000 (proper)
		else if (substr($prop[0],1,1) == '#') { $tmp = $prop[1]; $prop[0] = $prop[2]; $prop[1] = $prop[0]; $prop[2] = $tmp; }
		// Change solid 1px #000000 to 1px solid #000000 (proper)
		else if (in_array($prop[0],$this->borderstyles) || $prop[0] == 'none' || $prop[0] == 'hidden' ) { 
			$tmp = $prop[0]; $prop[0] = $prop[1]; $prop[1] = $tmp; 
		}
	}
	else { return array(); } 
	// Size
	$bsize = $this->ConvertSize($prop[0],$refw,$this->FontSize,false);
	//color
	$coul = $this->ConvertColor($prop[2]);	// returns array
	// Style
	$prop[1] = strtolower($prop[1]);
	if (in_array($prop[1],$this->borderstyles) && $bsize > 0) { $on = 1; } 
	else if ($prop[1] == 'hidden') { $on = 1; $bsize = 0; $coul = ''; } 
	else if ($prop[1] == 'none') { $on = 0; $bsize = 0; $coul = ''; } 
	else { $on = 0; $bsize = 0; $coul = ''; $prop[1] = ''; }
	return array('s' => $on, 'w' => $bsize, 'c' => $coul, 'style'=> $prop[1] );
}



/*-- END HTML-CSS --*/


// Return either a number (factor) - based on current set fontsize (if % or em) - or exact lineheight (with 'mm' after it)
function fixLineheight($v) {
	$lh = false;
	if (preg_match('/^[0-9\.,]*$/',$v) && $v >= 0) { return ($v + 0); }
	else if (strtoupper($v) == 'NORMAL') { 
		return $this->normalLineheight; 
	}
	else { 
		$tlh = $this->ConvertSize($v,$this->FontSize,$this->FontSize,true); 
		if ($tlh) { return ($tlh.'mm'); }
	}
	return $this->normalLineheight;
}


/*-- BORDER-RADIUS --*/
function _borderPadding($a, $b, &$px, &$py) {
	// $px and py are padding long axis (x) and short axis (y)
	$added = 0;	// extra padding

	$x = $a-$px;
	$y = $b-$py;
	// Check if Falls within ellipse of border radius
	if ( ( (($x+$added)*($x+$added))/($a*$a) + (($y+$added)*($y+$added))/($b*$b) ) <=1 ) { return false; }

	$t = atan2($y,$x);

	$newx = $b / sqrt((($b*$b)/($a*$a)) + ( tan($t) * tan($t) )  );
	$newy = $a / sqrt((($a*$a)/($b*$b)) + ( (1/tan($t)) * (1/tan($t)) )  );
	$px = max($px, $a - $newx + $added);
	$py = max($py, $b - $newy + $added);
}
/*-- END BORDER-RADIUS --*/



/*-- HTML-CSS --*/


/*-- CSS-PAGE --*/
function SetPagedMediaCSS($name='', $first, $oddEven) {
	if ($oddEven == 'E') { 
		if ($this->directionality=='rtl') { $side = 'R'; }
		else { $side = 'L'; }
	}
	else  { 
		if ($this->directionality=='rtl') { $side = 'L'; }
		else { $side = 'R'; }
	}
	$name = strtoupper($name);
	$p = array();
	$p['SIZE'] = 'AUTO';

	// Uses mPDF original margins as default
	$p['MARGIN-RIGHT'] = strval($this->orig_rMargin).'mm';
	$p['MARGIN-LEFT'] = strval($this->orig_lMargin).'mm';
	$p['MARGIN-TOP'] = strval($this->orig_tMargin).'mm';
	$p['MARGIN-BOTTOM'] = strval($this->orig_bMargin).'mm';
	$p['MARGIN-HEADER'] = strval($this->orig_hMargin).'mm';
	$p['MARGIN-FOOTER'] = strval($this->orig_fMargin).'mm';

	// Basic page + selector
	if (isset($this->cssmgr->CSS['@PAGE'])) { $zp = $this->cssmgr->CSS['@PAGE']; }
	else { $zp = array(); }
	if (is_array($zp) && !empty($zp)) { $p = array_merge($p,$zp); }

	if (isset($p['EVEN-HEADER-NAME']) && $oddEven=='E') { 
		$p['HEADER'] = $p['EVEN-HEADER-NAME']; unset($p['EVEN-HEADER-NAME']);
	}
	if (isset($p['ODD-HEADER-NAME']) && $oddEven!='E') { 
		$p['HEADER'] = $p['ODD-HEADER-NAME']; unset($p['ODD-HEADER-NAME']);
	}
	if (isset($p['EVEN-FOOTER-NAME']) && $oddEven=='E') { 
		$p['FOOTER'] = $p['EVEN-FOOTER-NAME']; unset($p['EVEN-FOOTER-NAME']);
	}
	if (isset($p['ODD-FOOTER-NAME']) && $oddEven!='E') { 
		$p['FOOTER'] = $p['ODD-FOOTER-NAME']; unset($p['ODD-FOOTER-NAME']);
	}

	// If right/Odd page
	if (isset($this->cssmgr->CSS['@PAGE>>PSEUDO>>RIGHT']) && $side=='R') { 
		$zp = $this->cssmgr->CSS['@PAGE>>PSEUDO>>RIGHT']; 
	}
	else { $zp = array(); }
	if (isset($zp['SIZE'])) { unset($zp['SIZE']); } 
	if (isset($zp['SHEET-SIZE'])) { unset($zp['SHEET-SIZE']); } 
	// Disallow margin-left or -right on :LEFT or :RIGHT
	if (isset($zp['MARGIN-LEFT'])) { unset($zp['MARGIN-LEFT']); } 
	if (isset($zp['MARGIN-RIGHT'])) { unset($zp['MARGIN-RIGHT']); } 
	if (is_array($zp) && !empty($zp)) { $p = array_merge($p,$zp); }

	// If left/Even page
	if (isset($this->cssmgr->CSS['@PAGE>>PSEUDO>>LEFT']) && $side=='L') {  
		$zp = $this->cssmgr->CSS['@PAGE>>PSEUDO>>LEFT']; 
	}
	else { $zp = array(); }
	if (isset($zp['SIZE'])) { unset($zp['SIZE']); } 
	if (isset($zp['SHEET-SIZE'])) { unset($zp['SHEET-SIZE']); } 
	// Disallow margin-left or -right on :LEFT or :RIGHT
	if (isset($zp['MARGIN-LEFT'])) { unset($zp['MARGIN-LEFT']); } 
	if (isset($zp['MARGIN-RIGHT'])) { unset($zp['MARGIN-RIGHT']); } 
	if (is_array($zp) && !empty($zp)) { $p = array_merge($p,$zp);  }

	// If first page
	if (isset($this->cssmgr->CSS['@PAGE>>PSEUDO>>FIRST']) && $first) { $zp = $this->cssmgr->CSS['@PAGE>>PSEUDO>>FIRST']; } 
	else { $zp = array(); }
	if (isset($zp['SIZE'])) { unset($zp['SIZE']); } 
	if (isset($zp['SHEET-SIZE'])) { unset($zp['SHEET-SIZE']); }
	if (is_array($zp) && !empty($zp)) { $p = array_merge($p,$zp); }

	// If named page
	if ($name) {
		if (isset($this->cssmgr->CSS['@PAGE>>NAMED>>'.$name])) { $zp = $this->cssmgr->CSS['@PAGE>>NAMED>>'.$name]; } 
		else { $zp = array(); }
		if (is_array($zp) && !empty($zp)) { $p = array_merge($p,$zp); }

		if (isset($p['EVEN-HEADER-NAME']) && $oddEven=='E') { 
			$p['HEADER'] = $p['EVEN-HEADER-NAME']; unset($p['EVEN-HEADER-NAME']);
		}
		if (isset($p['ODD-HEADER-NAME']) && $oddEven!='E') { 
			$p['HEADER'] = $p['ODD-HEADER-NAME']; unset($p['ODD-HEADER-NAME']);
		}
		if (isset($p['EVEN-FOOTER-NAME']) && $oddEven=='E') { 
			$p['FOOTER'] = $p['EVEN-FOOTER-NAME']; unset($p['EVEN-FOOTER-NAME']);
		}
		if (isset($p['ODD-FOOTER-NAME']) && $oddEven!='E') { 
			$p['FOOTER'] = $p['ODD-FOOTER-NAME']; unset($p['ODD-FOOTER-NAME']);
		}

		// If named right/Odd page
		if (isset($this->cssmgr->CSS['@PAGE>>NAMED>>'.$name.'>>PSEUDO>>RIGHT']) && $side=='R') { $zp = $this->cssmgr->CSS['@PAGE>>NAMED>>'.$name.'>>PSEUDO>>RIGHT']; } 
		else { $zp = array(); }
		if (isset($zp['SIZE'])) { unset($zp['SIZE']); } 
		if (isset($zp['SHEET-SIZE'])) { unset($zp['SHEET-SIZE']); } 
		// Disallow margin-left or -right on :LEFT or :RIGHT
		if (isset($zp['MARGIN-LEFT'])) { unset($zp['MARGIN-LEFT']); } 
		if (isset($zp['MARGIN-RIGHT'])) { unset($zp['MARGIN-RIGHT']); } 
		if (is_array($zp) && !empty($zp)) { $p = array_merge($p,$zp); }

		// If named left/Even page
		if (isset($this->cssmgr->CSS['@PAGE>>NAMED>>'.$name.'>>PSEUDO>>LEFT']) && $side=='L') { $zp = $this->cssmgr->CSS['@PAGE>>NAMED>>'.$name.'>>PSEUDO>>LEFT']; } 
		else { $zp = array(); }
		if (isset($zp['SIZE'])) { unset($zp['SIZE']); } 
		if (isset($zp['SHEET-SIZE'])) { unset($zp['SHEET-SIZE']); } 
		// Disallow margin-left or -right on :LEFT or :RIGHT
		if (isset($zp['MARGIN-LEFT'])) { unset($zp['MARGIN-LEFT']); } 
		if (isset($zp['MARGIN-RIGHT'])) { unset($zp['MARGIN-RIGHT']); } 
		if (is_array($zp) && !empty($zp)) { $p = array_merge($p,$zp); }

		// If named first page
		if (isset($this->cssmgr->CSS['@PAGE>>NAMED>>'.$name.'>>PSEUDO>>FIRST']) && $first) { $zp = $this->cssmgr->CSS['@PAGE>>NAMED>>'.$name.'>>PSEUDO>>FIRST']; } 
		else { $zp = array(); }
		if (isset($zp['SIZE'])) { unset($zp['SIZE']); } 
		if (isset($zp['SHEET-SIZE'])) { unset($zp['SHEET-SIZE']); }
		if (is_array($zp) && !empty($zp)) { $p = array_merge($p,$zp); }
	}

	$orientation = $mgl = $mgr = $mgt = $mgb = $mgh = $mgf = '';
	$header = $footer = '';
	$resetpagenum = $pagenumstyle = $suppress = '';
	$marks = ''; 
	$bg = array();
 
	$newformat = '';

 
	if (isset($p['SHEET-SIZE']) && is_array($p['SHEET-SIZE'])) {
		$newformat = $p['SHEET-SIZE'];
		if ($newformat[0] > $newformat[1]) { // landscape
			$newformat = array_reverse($newformat);
			$p['ORIENTATION'] = 'L';
		}
		else { $p['ORIENTATION'] = 'P'; }
		$this->_setPageSize($newformat, $p['ORIENTATION']);
	}

	if (isset($p['SIZE']) && is_array($p['SIZE']) && !$newformat) {
		if ($p['SIZE']['W'] > $p['SIZE']['H']) { $p['ORIENTATION'] = 'L'; }
		else { $p['ORIENTATION'] = 'P'; }
	}
	if (is_array($p['SIZE'])) {
		if ($p['SIZE']['W'] > $this->fw) { $p['SIZE']['W'] = $this->fw; }	// mPD 4.2 use fw not fPt
		if ($p['SIZE']['H'] > $this->fh) { $p['SIZE']['H'] = $this->fh; }
		if (($p['ORIENTATION']==$this->DefOrientation && !$newformat) || ($newformat && $p['ORIENTATION']=='P')) { 
			$outer_width_LR = ($this->fw - $p['SIZE']['W'])/2;
			$outer_width_TB = ($this->fh - $p['SIZE']['H'])/2;
		}
		else {
			$outer_width_LR = ($this->fh - $p['SIZE']['W'])/2;
			$outer_width_TB = ($this->fw - $p['SIZE']['H'])/2;
		}
		$pgw = $p['SIZE']['W'];
		$pgh = $p['SIZE']['H'];
	}
	else {	// AUTO LANDSCAPE PORTRAIT
		$outer_width_LR = 0;
		$outer_width_TB = 0;
		if (!$newformat) {
			if (strtoupper($p['SIZE']) == 'AUTO') { $p['ORIENTATION']=$this->DefOrientation; }
			else if (strtoupper($p['SIZE']) == 'LANDSCAPE') { $p['ORIENTATION']='L'; }
			else { $p['ORIENTATION']='P'; }
		}
		if (($p['ORIENTATION']==$this->DefOrientation && !$newformat) || ($newformat && $p['ORIENTATION']=='P')) { 
			$pgw = $this->fw;
			$pgh = $this->fh;
		}
		else {
			$pgw = $this->fh;
			$pgh = $this->fw;
		}
	}

	if (isset($p['HEADER']) && $p['HEADER']) { $header = $p['HEADER']; }
	if (isset($p['FOOTER']) && $p['FOOTER']) { $footer = $p['FOOTER']; }
	if (isset($p['RESETPAGENUM']) && $p['RESETPAGENUM']) { $resetpagenum = $p['RESETPAGENUM']; }
	if (isset($p['PAGENUMSTYLE']) && $p['PAGENUMSTYLE']) { $pagenumstyle = $p['PAGENUMSTYLE']; }
	if (isset($p['SUPPRESS']) && $p['SUPPRESS']) { $suppress = $p['SUPPRESS']; }

  	if (preg_match('/cross/i', $p['MARKS']) && preg_match('/crop/i', $p['MARKS'])) { $marks = 'CROPCROSS'; }
  	else if (strtoupper($p['MARKS']) == 'CROP') { $marks = 'CROP'; }
  	else if (strtoupper($p['MARKS']) == 'CROSS') { $marks = 'CROSS'; }


	if (isset($p['BACKGROUND-COLOR']) && $p['BACKGROUND-COLOR']) { $bg['BACKGROUND-COLOR'] = $p['BACKGROUND-COLOR']; }
/*-- BACKGROUNDS --*/
	if (isset($p['BACKGROUND-GRADIENT']) && $p['BACKGROUND-GRADIENT']) { $bg['BACKGROUND-GRADIENT'] = $p['BACKGROUND-GRADIENT']; }
	if (isset($p['BACKGROUND-IMAGE']) && $p['BACKGROUND-IMAGE']) { $bg['BACKGROUND-IMAGE'] = $p['BACKGROUND-IMAGE']; }
	if (isset($p['BACKGROUND-REPEAT']) && $p['BACKGROUND-REPEAT']) { $bg['BACKGROUND-REPEAT'] = $p['BACKGROUND-REPEAT']; }
	if (isset($p['BACKGROUND-POSITION']) && $p['BACKGROUND-POSITION']) { $bg['BACKGROUND-POSITION'] = $p['BACKGROUND-POSITION']; }
	if (isset($p['BACKGROUND-IMAGE-RESIZE']) && $p['BACKGROUND-IMAGE-RESIZE']) { $bg['BACKGROUND-IMAGE-RESIZE'] = $p['BACKGROUND-IMAGE-RESIZE']; }
	if (isset($p['BACKGROUND-IMAGE-OPACITY'])) { $bg['BACKGROUND-IMAGE-OPACITY'] = $p['BACKGROUND-IMAGE-OPACITY']; }
/*-- END BACKGROUNDS --*/

	if (isset($p['MARGIN-LEFT'])) { $mgl = $this->ConvertSize($p['MARGIN-LEFT'],$pgw) + $outer_width_LR; }
	if (isset($p['MARGIN-RIGHT'])) { $mgr = $this->ConvertSize($p['MARGIN-RIGHT'],$pgw) + $outer_width_LR; }
	if (isset($p['MARGIN-BOTTOM'])) { $mgb = $this->ConvertSize($p['MARGIN-BOTTOM'],$pgh) + $outer_width_TB; }
	if (isset($p['MARGIN-TOP'])) { $mgt = $this->ConvertSize($p['MARGIN-TOP'],$pgh) + $outer_width_TB; }
	if (isset($p['MARGIN-HEADER'])) { $mgh = $this->ConvertSize($p['MARGIN-HEADER'],$pgh) + $outer_width_TB; }
	if (isset($p['MARGIN-FOOTER'])) { $mgf = $this->ConvertSize($p['MARGIN-FOOTER'],$pgh) + $outer_width_TB; }

	if (isset($p['ORIENTATION']) && $p['ORIENTATION']) { $orientation = $p['ORIENTATION']; }
	$this->page_box['outer_width_LR'] = $outer_width_LR;	// Used in MARKS:crop etc.
	$this->page_box['outer_width_TB'] = $outer_width_TB;
 
	return array($orientation,$mgl,$mgr,$mgt,$mgb,$mgh,$mgf,$header,$footer,$bg,$resetpagenum,$pagenumstyle,$suppress,$marks,$newformat);
}
/*-- END CSS-PAGE --*/



/*-- CSS-FLOAT --*/
// Added mPDF 3.0 Float DIV - CLEAR
function ClearFloats($clear, $blklvl=0) {
	list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($blklvl,true);
	$end = $currpos = ($this->page*1000 + $this->y);
	if ($clear == 'BOTH' && ($l_exists || $r_exists)) {
		$this->pageoutput[$this->page] = array();
		$end = max($l_max, $r_max, $currpos);
	}
	else if ($clear == 'RIGHT' && $r_exists) {
		$this->pageoutput[$this->page] = array();
		$end = max($r_max, $currpos);
	}
	else if ($clear == 'LEFT' && $l_exists ) {
		$this->pageoutput[$this->page] = array();
		$end = max($l_max, $currpos);
	}
	else { return; }
	$old_page = $this->page;
	$new_page = intval($end/1000);
	if ($old_page != $new_page) {
		$s = $this->PrintPageBackgrounds();
		// Writes after the marker so not overwritten later by page background etc.
		$this->pages[$this->page] = preg_replace('/(___BACKGROUND___PATTERNS'.date('jY').')/', '\\1'."\n".$s."\n", $this->pages[$this->page]);
		$this->pageBackgrounds = array();
		$this->page = $new_page;
	}
	$this->ResetMargins();
	$this->pageoutput[$this->page] = array();
	$this->y = (($end*1000) % 1000000)/1000;	// mod changes operands to integers before processing
}


// Added mPDF 3.0 Float DIV
function GetFloatDivInfo($blklvl=0,$clear=false) {
	// If blklvl specified, only returns floats at that level - for ClearFloats
	$l_exists = false;
	$r_exists = false;
	$l_max = 0;
	$r_max = 0;
	$l_width = 0;
	$r_width = 0;
	if (count($this->floatDivs)) {
	  $currpos = ($this->page*1000 + $this->y);
	  foreach($this->floatDivs AS $f) {
	    if (($clear && $f['blockContext'] == $this->blk[$blklvl]['blockContext']) || (!$clear && $currpos >= $f['startpos'] && $currpos < ($f['endpos']-0.001) && $f['blklvl'] > $blklvl && $f['blockContext'] == $this->blk[$blklvl]['blockContext'])) {
		if ($f['side']=='L') {
			$l_exists= true;
			$l_max = max($l_max, $f['endpos']);
			$l_width = max($l_width , $f['w']);
		}
		if ($f['side']=='R') {
			$r_exists= true;
			$r_max = max($r_max, $f['endpos']);
			$r_width = max($r_width , $f['w']);
		}
	    }
	  }
	}
	return array($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width);
}
/*-- END CSS-FLOAT --*/




function OpenTag($tag,$attr)
{

  // What this gets: < $tag $attr['WIDTH']="90px" > does not get content here </closeTag here>
  // Correct tags where HTML specifies optional end tags,
  // and/or does not allow nesting e.g. P inside P, or 
  if ($this->allow_html_optional_endtags) {
    if (($tag == 'P' || $tag == 'DIV' || $tag == 'H1' || $tag == 'H2' || $tag == 'H3' || $tag == 'H4' || $tag == 'H5' || $tag == 'H6' || $tag == 'UL' || $tag == 'OL' || $tag == 'TABLE' || $tag=='PRE' || $tag=='FORM' || $tag=='ADDRESS' || $tag=='BLOCKQUOTE' || $tag=='CENTER' || $tag=='DL' || $tag == 'HR' ) && $this->lastoptionaltag == 'P') { $this->CloseTag($this->lastoptionaltag ); }
    if ($tag == 'DD' && $this->lastoptionaltag == 'DD') { $this->CloseTag($this->lastoptionaltag ); }
    if ($tag == 'DD' && $this->lastoptionaltag == 'DT') { $this->CloseTag($this->lastoptionaltag ); }
    if ($tag == 'DT' && $this->lastoptionaltag == 'DD') { $this->CloseTag($this->lastoptionaltag ); }
    if ($tag == 'DT' && $this->lastoptionaltag == 'DT') { $this->CloseTag($this->lastoptionaltag ); }
    if ($tag == 'LI' && $this->lastoptionaltag == 'LI') { $this->CloseTag($this->lastoptionaltag ); }
    if (($tag == 'TD' || $tag == 'TH') && $this->lastoptionaltag == 'TD') { $this->CloseTag($this->lastoptionaltag ); }	// *TABLES*
    if (($tag == 'TD' || $tag == 'TH') && $this->lastoptionaltag == 'TH') { $this->CloseTag($this->lastoptionaltag ); }	// *TABLES*
    if ($tag == 'TR' && $this->lastoptionaltag == 'TR') { $this->CloseTag($this->lastoptionaltag ); }	// *TABLES*
    if ($tag == 'TR' && $this->lastoptionaltag == 'TD') { $this->CloseTag($this->lastoptionaltag );  $this->CloseTag('TR'); $this->CloseTag('THEAD'); }	// *TABLES*
    if ($tag == 'TR' && $this->lastoptionaltag == 'TH') { $this->CloseTag($this->lastoptionaltag );  $this->CloseTag('TR'); $this->CloseTag('THEAD'); }	// *TABLES*
    if ($tag == 'OPTION' && $this->lastoptionaltag == 'OPTION') { $this->CloseTag($this->lastoptionaltag ); }
  }

  $align = array('left'=>'L','center'=>'C','right'=>'R','top'=>'T','text-top'=>'TT','middle'=>'M','baseline'=>'BS','bottom'=>'B','text-bottom'=>'TB','justify'=>'J');

  $this->ignorefollowingspaces=false;

  //Opening tag
  switch($tag){

     case 'DOTTAB': 
	$objattr = array();
	$objattr['type'] = 'dottab';
	$dots=str_repeat('.', 3)."  ";	// minimum number of dots
	$objattr['width'] = $this->GetStringWidth($dots);
	$objattr['margin_top'] = 0;
	$objattr['margin_bottom'] = 0;
	$objattr['margin_left'] = 0;
	$objattr['margin_right'] = 0;
	$objattr['height'] = 0;
	$objattr['colorarray'] = $this->colorarray;
	$objattr['border_top']['w'] = 0;
	$objattr['border_bottom']['w'] = 0;
	$objattr['border_left']['w'] = 0;
	$objattr['border_right']['w'] = 0;

	// mPDF 5.6.19
	$properties = $this->cssmgr->MergeCSS('INLINE',$tag,$attr);	// mPDF 5.6.33
	if (isset($properties['OUTDENT'])) {	// mPDF 5.6.33
		$objattr['outdent'] = $this->ConvertSize($properties['OUTDENT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
	}
	else if (isset($attr['OUTDENT'])) {
		$objattr['outdent'] = $this->ConvertSize($attr['OUTDENT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
	}
	else { $objattr['outdent'] = 0; }

	$objattr['fontfamily'] = $this->FontFamily;
	$objattr['fontsize'] = $this->FontSizePt;

	$e = "\xbb\xa4\xactype=dottab,objattr=".serialize($objattr)."\xbb\xa4\xac";
/*-- TABLES --*/
	// Output it to buffers
	if ($this->tableLevel) {
		if (!isset($this->cell[$this->row][$this->col]['maxs'])) {
			$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s'];
		}
		elseif($this->cell[$this->row][$this->col]['maxs'] < $this->cell[$this->row][$this->col]['s']) {
			$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s']; 
		}
		$this->cell[$this->row][$this->col]['s'] = 0 ;// reset
		$this->_saveCellTextBuffer($e);
	}
	else {
/*-- END TABLES --*/
		$this->_saveTextBuffer($e);
	}	// *TABLES*
	break;

     case 'PAGEHEADER': 
     case 'PAGEFOOTER':
	$this->ignorefollowingspaces = true; 
	if ($attr['NAME']) { $pname = $attr['NAME']; }
	else { $pname = '_default'; }

		if ($tag=='PAGEHEADER') { $p = &$this->pageheaders[$pname]; }
		else { $p = &$this->pagefooters[$pname]; }

		$p['L']=array();
		$p['C']=array();
		$p['R']=array();
		$p['L']['font-style'] = ''; 
		$p['C']['font-style'] = ''; 
		$p['R']['font-style'] = ''; 

		if (isset($attr['CONTENT-LEFT'])) {
			$p['L']['content'] = $attr['CONTENT-LEFT'];
		}
		if (isset($attr['CONTENT-CENTER'])) {
			$p['C']['content'] = $attr['CONTENT-CENTER'];
		}
		if (isset($attr['CONTENT-RIGHT'])) {
			$p['R']['content'] = $attr['CONTENT-RIGHT'];
		}

		if (isset($attr['HEADER-STYLE']) || isset($attr['FOOTER-STYLE'])) {	// font-family,size,weight,style,color
			if ($tag=='PAGEHEADER') { $properties = $this->cssmgr->readInlineCSS($attr['HEADER-STYLE']); }
			else { $properties = $this->cssmgr->readInlineCSS($attr['FOOTER-STYLE']); }
			if (isset($properties['FONT-FAMILY'])) { 
				$p['L']['font-family'] = $properties['FONT-FAMILY']; 
				$p['C']['font-family'] = $properties['FONT-FAMILY']; 
				$p['R']['font-family'] = $properties['FONT-FAMILY']; 
			}
			if (isset($properties['FONT-SIZE'])) { 
				$p['L']['font-size'] = $this->ConvertSize($properties['FONT-SIZE']) * _MPDFK; 
				$p['C']['font-size'] = $this->ConvertSize($properties['FONT-SIZE']) * _MPDFK; 
				$p['R']['font-size'] = $this->ConvertSize($properties['FONT-SIZE']) * _MPDFK; 
			}
			if (isset($properties['FONT-WEIGHT']) && $properties['FONT-WEIGHT']=='bold') {
				$p['L']['font-style'] = 'B'; 
				$p['C']['font-style'] = 'B'; 
				$p['R']['font-style'] = 'B'; 
			}
			if (isset($properties['FONT-STYLE']) && $properties['FONT-STYLE']=='italic') { 
				$p['L']['font-style'] .= 'I'; 
				$p['C']['font-style'] .= 'I'; 
				$p['R']['font-style'] .= 'I'; 
			}
			if (isset($properties['COLOR'])) { 
				$p['L']['color'] = $properties['COLOR']; 
				$p['C']['color'] = $properties['COLOR']; 
				$p['R']['color'] = $properties['COLOR']; 
			}
		}
		if (isset($attr['HEADER-STYLE-LEFT']) || isset($attr['FOOTER-STYLE-LEFT'])) {
			if ($tag=='PAGEHEADER') { $properties = $this->cssmgr->readInlineCSS($attr['HEADER-STYLE-LEFT']); }
			else { $properties = $this->cssmgr->readInlineCSS($attr['FOOTER-STYLE-LEFT']); }
			if (isset($properties['FONT-FAMILY'])) { $p['L']['font-family'] = $properties['FONT-FAMILY']; }
			if (isset($properties['FONT-SIZE'])) { $p['L']['font-size'] = $this->ConvertSize($properties['FONT-SIZE']) * _MPDFK; }
			if (isset($properties['FONT-WEIGHT']) && $properties['FONT-WEIGHT']=='bold') { $p['L']['font-style'] ='B'; }
			if (isset($properties['FONT-STYLE']) && $properties['FONT-STYLE']=='italic') { $p['L']['font-style'] .='I'; }
			if (isset($properties['COLOR'])) { $p['L']['color'] = $properties['COLOR']; }
		}
		if (isset($attr['HEADER-STYLE-CENTER']) || isset($attr['FOOTER-STYLE-CENTER'])) {
			if ($tag=='PAGEHEADER') { $properties = $this->cssmgr->readInlineCSS($attr['HEADER-STYLE-CENTER']); }
			else { $properties = $this->cssmgr->readInlineCSS($attr['FOOTER-STYLE-CENTER']); }
			if (isset($properties['FONT-FAMILY'])) { $p['C']['font-family'] = $properties['FONT-FAMILY']; }
			if (isset($properties['FONT-SIZE'])) { $p['C']['font-size'] = $this->ConvertSize($properties['FONT-SIZE']) * _MPDFK; }
			if (isset($properties['FONT-WEIGHT']) && $properties['FONT-WEIGHT']=='bold') { $p['C']['font-style'] = 'B'; }
			if (isset($properties['FONT-STYLE']) && $properties['FONT-STYLE']=='italic') { $p['C']['font-style'] .= 'I'; }
			if (isset($properties['COLOR'])) { $p['C']['color'] = $properties['COLOR']; }
		}
		if (isset($attr['HEADER-STYLE-RIGHT']) || isset($attr['FOOTER-STYLE-RIGHT'])) {
			if ($tag=='PAGEHEADER') { $properties = $this->cssmgr->readInlineCSS($attr['HEADER-STYLE-RIGHT']); }
			else { $properties = $this->cssmgr->readInlineCSS($attr['FOOTER-STYLE-RIGHT']); }
			if (isset($properties['FONT-FAMILY'])) { $p['R']['font-family'] = $properties['FONT-FAMILY']; }
			if (isset($properties['FONT-SIZE'])) { $p['R']['font-size'] = $this->ConvertSize($properties['FONT-SIZE']) * _MPDFK; }
			if (isset($properties['FONT-WEIGHT']) && $properties['FONT-WEIGHT']=='bold') { $p['R']['font-style'] = 'B'; }
			if (isset($properties['FONT-STYLE']) && $properties['FONT-STYLE']=='italic') { $p['R']['font-style'] .= 'I'; }
			if (isset($properties['COLOR'])) { $p['R']['color'] = $properties['COLOR']; }
		}
		if (isset($attr['LINE']) && $attr['LINE']) {	// 0|1|on|off
			if ($attr['LINE']=='1' || strtoupper($attr['LINE'])=='ON') { $lineset=1; }
			else { $lineset=0; }
			$p['line'] = $lineset;
		}
	break;


/*-- HTMLHEADERS-FOOTERS --*/
     case 'SETHTMLPAGEHEADER': 
     case 'SETHTMLPAGEFOOTER':
	$this->ignorefollowingspaces = true; 
	if (isset($attr['NAME']) && $attr['NAME']) { $pname = $attr['NAME']; }
	else { $pname = '_default'; }
	if (isset($attr['PAGE']) && $attr['PAGE']) { 	// O|odd|even|E|ALL|[blank]
		if (strtoupper($attr['PAGE'])=='O' || strtoupper($attr['PAGE'])=='ODD') { $side='odd'; }
		else if (strtoupper($attr['PAGE'])=='E' || strtoupper($attr['PAGE'])=='EVEN') { $side='even'; }
		else if (strtoupper($attr['PAGE'])=='ALL') { $side='both'; }
		else { $side='odd'; }
	}
	else { $side='odd'; }
	if (isset($attr['VALUE']) && $attr['VALUE']) { 	// -1|1|on|off
		if ($attr['VALUE']=='1' || strtoupper($attr['VALUE'])=='ON') { $set=1; }
		else if ($attr['VALUE']=='-1' || strtoupper($attr['VALUE'])=='OFF') { $set=0; }
		else { $set=1; }
	}
	else { $set=1; }
	if (isset($attr['SHOW-THIS-PAGE']) && $attr['SHOW-THIS-PAGE'] && $tag=='SETHTMLPAGEHEADER') { $write = 1; }
	else { $write = 0; }
	if ($side=='odd' || $side=='both') {
		if ($set && $tag=='SETHTMLPAGEHEADER') { $this->SetHTMLHeader($this->pageHTMLheaders[$pname],'O',$write); }
		else if ($set && $tag=='SETHTMLPAGEFOOTER') { $this->SetHTMLFooter($this->pageHTMLfooters[$pname],'O'); }
		else if ($tag=='SETHTMLPAGEHEADER') { $this->SetHTMLHeader('','O'); }
		else { $this->SetHTMLFooter('','O'); }
	}
	if ($side=='even' || $side=='both') {
		if ($set && $tag=='SETHTMLPAGEHEADER') { $this->SetHTMLHeader($this->pageHTMLheaders[$pname],'E',$write); }
		else if ($set && $tag=='SETHTMLPAGEFOOTER') { $this->SetHTMLFooter($this->pageHTMLfooters[$pname],'E'); }
		else if ($tag=='SETHTMLPAGEHEADER') { $this->SetHTMLHeader('','E'); }
		else { $this->SetHTMLFooter('','E'); }
	}
	break;
/*-- END HTMLHEADERS-FOOTERS --*/

     case 'SETPAGEHEADER': 
     case 'SETPAGEFOOTER':
	$this->ignorefollowingspaces = true; 
	if (isset($attr['NAME']) && $attr['NAME']) { $pname = $attr['NAME']; }
	else { $pname = '_default'; }
	if (isset($attr['PAGE']) && $attr['PAGE']) { 	// O|odd|even|E|ALL|[blank]
		if (strtoupper($attr['PAGE'])=='O' || strtoupper($attr['PAGE'])=='ODD') { $side='odd'; }
		else if (strtoupper($attr['PAGE'])=='E' || strtoupper($attr['PAGE'])=='EVEN') { $side='even'; }
		else if (strtoupper($attr['PAGE'])=='ALL') { $side='both'; }
		else { $side='odd'; }
	}
	else { $side='odd'; }
	if (isset($attr['VALUE']) && $attr['VALUE']) { 	// -1|1|on|off
		if ($attr['VALUE']=='1' || strtoupper($attr['VALUE'])=='ON') { $set=1; }
		else if ($attr['VALUE']=='-1' || strtoupper($attr['VALUE'])=='OFF') { $set=0; }
		else { $set=1; }
	}
	else { $set=1; }
	if ($side=='odd' || $side=='both') {
		if ($set && $tag=='SETPAGEHEADER') { $this->headerDetails['odd'] = $this->pageheaders[$pname]; }
		else if ($set && $tag=='SETPAGEFOOTER') { $this->footerDetails['odd'] = $this->pagefooters[$pname]; }
		else if ($tag=='SETPAGEHEADER') { $this->headerDetails['odd'] = array(); }
		else { $this->footerDetails['odd'] = array(); }
		if (!$this->mirrorMargins || ($this->page)%2!=0) {	// ODD
			if ($tag=='SETPAGEHEADER') { $this->_setAutoHeaderHeight($this->headerDetails['odd'],$this->HTMLHeader); }
			if ($tag=='SETPAGEFOOTER') { $this->_setAutoFooterHeight($this->footerDetails['odd'],$this->HTMLFooter); }
		}
	}
	if ($side=='even' || $side=='both') {
		if ($set && $tag=='SETPAGEHEADER') { $this->headerDetails['even'] = $this->pageheaders[$pname]; }
		else if ($set && $tag=='SETPAGEFOOTER') { $this->footerDetails['even'] = $this->pagefooters[$pname]; }
		else if ($tag=='SETPAGEHEADER') { $this->headerDetails['even'] = array(); }
		else { $this->footerDetails['even'] = array(); }
		if ($this->mirrorMargins && ($this->page)%2==0) {	// EVEN
			if ($tag=='SETPAGEHEADER') { $this->_setAutoHeaderHeight($this->headerDetails['even'],$this->HTMLHeaderE); }
			if ($tag=='SETPAGEFOOTER') { $this->_setAutoFooterHeight($this->footerDetails['even'],$this->HTMLFooterE); }
		}
	}
	if (isset($attr['SHOW-THIS-PAGE']) && $attr['SHOW-THIS-PAGE'] && $tag=='SETPAGEHEADER') {
		$this->Header();
	}
	break;


/*-- TOC --*/
     case 'TOC': //added custom-tag - set Marker for insertion later of ToC
	if (!class_exists('tocontents', false)) { include(_MPDF_PATH.'classes/tocontents.php'); }
	if (empty($this->tocontents)) { $this->tocontents = new tocontents($this); }
	$this->tocontents->openTagTOC($attr);
	break;



     case 'TOCPAGEBREAK': // custom-tag - set Marker for insertion later of ToC AND adds PAGEBREAK
	if (!class_exists('tocontents', false)) { include(_MPDF_PATH.'classes/tocontents.php'); }
	if (empty($this->tocontents)) { $this->tocontents = new tocontents($this); }
	list($isbreak,$toc_id) = $this->tocontents->openTagTOCPAGEBREAK($attr);
	if ($isbreak) break;
	// No break - continues as PAGEBREAK...
/*-- END TOC --*/


    case 'PAGE_BREAK': //custom-tag
    case 'PAGEBREAK': //custom-tag
    case 'NEWPAGE': //custom-tag
    case 'FORMFEED': //custom-tag

	$save_blklvl = $this->blklvl;
	$save_blk = $this->blk;
	$save_silp = $this->saveInlineProperties();
	$save_spanlvl = $this->spanlvl;
	$save_ilp = $this->InlineProperties;

	// Close any open block tags
	for ($b= $this->blklvl;$b>0;$b--) { $this->CloseTag($this->blk[$b]['tag']); }
	if(!empty($this->textbuffer))  {	//Output previously buffered content
   	  	$this->printbuffer($this->textbuffer);
        	$this->textbuffer=array(); 
      }
	$this->ignorefollowingspaces = true;
	$save_cols = false;
/*-- COLUMNS --*/
	if ($this->ColActive) {
		$save_cols = true;
		$save_nbcol = $this->NbCol;	// other values of gap and vAlign will not change by setting Columns off
		$this->SetColumns(0);
	}
/*-- END COLUMNS --*/


	if (isset($attr['SHEET-SIZE']) && $tag != 'FORMFEED' && !$this->restoreBlockPageBreaks) { 
		// Convert to same types as accepted in initial mPDF() A4, A4-L, or array(w,h)
		$prop = preg_split('/\s+/',trim($attr['SHEET-SIZE']));
		if (count($prop) == 2 ) {
			$newformat = array($this->ConvertSize($prop[0]), $this->ConvertSize($prop[1]));
		}
		else { $newformat = $attr['SHEET-SIZE']; }
	}
	else { $newformat = ''; }

	$mgr = $mgl = $mgt = $mgb = $mgh = $mgf = '';
	if (isset($attr['MARGIN-RIGHT'])) { $mgr = $this->ConvertSize($attr['MARGIN-RIGHT'],$this->w,$this->FontSize,false); }
	if (isset($attr['MARGIN-LEFT'])) { $mgl = $this->ConvertSize($attr['MARGIN-LEFT'],$this->w,$this->FontSize,false); }
	if (isset($attr['MARGIN-TOP'])) { $mgt = $this->ConvertSize($attr['MARGIN-TOP'],$this->w,$this->FontSize,false); }
	if (isset($attr['MARGIN-BOTTOM'])) { $mgb = $this->ConvertSize($attr['MARGIN-BOTTOM'],$this->w,$this->FontSize,false); }
	if (isset($attr['MARGIN-HEADER'])) { $mgh = $this->ConvertSize($attr['MARGIN-HEADER'],$this->w,$this->FontSize,false); }
	if (isset($attr['MARGIN-FOOTER'])) { $mgf = $this->ConvertSize($attr['MARGIN-FOOTER'],$this->w,$this->FontSize,false); }
	$ohname = $ehname = $ofname = $efname = '';
	if (isset($attr['ODD-HEADER-NAME'])) { $ohname = $attr['ODD-HEADER-NAME']; }
	if (isset($attr['EVEN-HEADER-NAME'])) { $ehname = $attr['EVEN-HEADER-NAME']; }
	if (isset($attr['ODD-FOOTER-NAME'])) { $ofname = $attr['ODD-FOOTER-NAME']; }
	if (isset($attr['EVEN-FOOTER-NAME'])) { $efname = $attr['EVEN-FOOTER-NAME']; }
	$ohvalue = $ehvalue = $ofvalue = $efvalue = 0;
	if (isset($attr['ODD-HEADER-VALUE']) && ($attr['ODD-HEADER-VALUE']=='1' || strtoupper($attr['ODD-HEADER-VALUE'])=='ON')) { $ohvalue = 1; }
	else if (isset($attr['ODD-HEADER-VALUE']) && ($attr['ODD-HEADER-VALUE']=='-1' || strtoupper($attr['ODD-HEADER-VALUE'])=='OFF')) { $ohvalue = -1; }
	if (isset($attr['EVEN-HEADER-VALUE']) && ($attr['EVEN-HEADER-VALUE']=='1' || strtoupper($attr['EVEN-HEADER-VALUE'])=='ON')) { $ehvalue = 1; }
	else if (isset($attr['EVEN-HEADER-VALUE']) && ($attr['EVEN-HEADER-VALUE']=='-1' || strtoupper($attr['EVEN-HEADER-VALUE'])=='OFF')) { $ehvalue = -1; }
	if (isset($attr['ODD-FOOTER-VALUE']) && ($attr['ODD-FOOTER-VALUE']=='1' || strtoupper($attr['ODD-FOOTER-VALUE'])=='ON')) { $ofvalue = 1; }
	else if (isset($attr['ODD-FOOTER-VALUE']) && ($attr['ODD-FOOTER-VALUE']=='-1' || strtoupper($attr['ODD-FOOTER-VALUE'])=='OFF')) { $ofvalue = -1; }
	if (isset($attr['EVEN-FOOTER-VALUE']) && ($attr['EVEN-FOOTER-VALUE']=='1' || strtoupper($attr['EVEN-FOOTER-VALUE'])=='ON')) { $efvalue = 1; }
	else if (isset($attr['EVEN-FOOTER-VALUE']) && ($attr['EVEN-FOOTER-VALUE']=='-1' || strtoupper($attr['EVEN-FOOTER-VALUE'])=='OFF')) { $efvalue = -1; }

	if (isset($attr['ORIENTATION']) && (strtoupper($attr['ORIENTATION'])=='L' || strtoupper($attr['ORIENTATION'])=='LANDSCAPE')) { $orient = 'L'; }
	else if (isset($attr['ORIENTATION']) && (strtoupper($attr['ORIENTATION'])=='P' || strtoupper($attr['ORIENTATION'])=='PORTRAIT')) { $orient = 'P'; }
	else { $orient = $this->CurOrientation; }

	if (isset($attr['PAGE-SELECTOR']) && $attr['PAGE-SELECTOR']) { $pagesel = $attr['PAGE-SELECTOR']; }
	else { $pagesel = ''; }

	$resetpagenum = '';
	$pagenumstyle = '';
	$suppress = '';
	if (isset($attr['RESETPAGENUM'])) { $resetpagenum = $attr['RESETPAGENUM']; }
	if (isset($attr['PAGENUMSTYLE'])) { $pagenumstyle = $attr['PAGENUMSTYLE']; }
	if (isset($attr['SUPPRESS'])) { $suppress = $attr['SUPPRESS']; }

	if ($tag == 'TOCPAGEBREAK') { $type = 'NEXT-ODD'; }
	else if(isset($attr['TYPE'])) { $type = strtoupper($attr['TYPE']); }
	else { $type = ''; }

	if ($type == 'E' || $type == 'EVEN') { $this->AddPage($orient,'E', $resetpagenum, $pagenumstyle, $suppress,$mgl,$mgr,$mgt,$mgb,$mgh,$mgf,$ohname,$ehname,$ofname,$efname,$ohvalue,$ehvalue,$ofvalue,$efvalue,$pagesel,$newformat); }
	else if ($type == 'O' || $type == 'ODD') { $this->AddPage($orient,'O', $resetpagenum, $pagenumstyle, $suppress,$mgl,$mgr,$mgt,$mgb,$mgh,$mgf,$ohname,$ehname,$ofname,$efname,$ohvalue,$ehvalue,$ofvalue,$efvalue,$pagesel,$newformat); }
	else if ($type == 'NEXT-ODD') { $this->AddPage($orient,'NEXT-ODD', $resetpagenum, $pagenumstyle, $suppress,$mgl,$mgr,$mgt,$mgb,$mgh,$mgf,$ohname,$ehname,$ofname,$efname,$ohvalue,$ehvalue,$ofvalue,$efvalue,$pagesel,$newformat); }
	else if ($type == 'NEXT-EVEN') { $this->AddPage($orient,'NEXT-EVEN', $resetpagenum, $pagenumstyle, $suppress,$mgl,$mgr,$mgt,$mgb,$mgh,$mgf,$ohname,$ehname,$ofname,$efname,$ohvalue,$ehvalue,$ofvalue,$efvalue,$pagesel,$newformat); }
	else { $this->AddPage($orient,'', $resetpagenum, $pagenumstyle, $suppress,$mgl,$mgr,$mgt,$mgb,$mgh,$mgf,$ohname,$ehname,$ofname,$efname,$ohvalue,$ehvalue,$ofvalue,$efvalue,$pagesel,$newformat); }

/*-- TOC --*/
	if ($tag == 'TOCPAGEBREAK') { 
		if ($toc_id) { $this->tocontents->m_TOC[$toc_id]['TOCmark'] = $this->page; }
		else { $this->tocontents->TOCmark = $this->page; }
	}
/*-- END TOC --*/

/*-- COLUMNS --*/
	if ($save_cols) {
		// Restore columns
		$this->SetColumns($save_nbcol,$this->colvAlign,$this->ColGap);
	}
/*-- END COLUMNS --*/
	if (($tag == 'FORMFEED' || $this->restoreBlockPagebreaks) && !$this->tableLevel && !$this->listlvl) {
		$this->blk = $save_blk;
		// Re-open block tags
		$t = $this->blk[0]['tag'];
		$a = $this->blk[0]['attr'];
		$this->blklvl = 0; 
		for ($b=0; $b<=$save_blklvl;$b++) {
			$tc = $t;
			$ac = $a;
			$t = $this->blk[$b+1]['tag'];
			$a = $this->blk[$b+1]['attr'];
			unset($this->blk[$b+1]);
			$this->OpenTag($tc,$ac); 
		}
		$this->spanlvl = $save_spanlvl;
		$this->InlineProperties = $save_ilp;
		$this->restoreInlineProperties($save_silp);
	}

	break;


/*-- TOC --*/
     case 'TOCENTRY':
	if (isset($attr['CONTENT']) && $attr['CONTENT']) {
		$objattr = array();
		$objattr['CONTENT'] = htmlspecialchars_decode($attr['CONTENT'],ENT_QUOTES);
		$objattr['type'] = 'toc';
		if (isset($attr['LEVEL']) && $attr['LEVEL']) { $objattr['toclevel'] = $attr['LEVEL']; } else { $objattr['toclevel'] = 0; }
		if (isset($attr['NAME']) && $attr['NAME']) { $objattr['toc_id'] = $attr['NAME']; } else { $objattr['toc_id'] = 0; }
		$e = "\xbb\xa4\xactype=toc,objattr=".serialize($objattr)."\xbb\xa4\xac";
		if($this->tableLevel) { $this->cell[$this->row][$this->col]['textbuffer'][] = array($e); }	// *TABLES*
		else  {	// *TABLES*
			$this->textbuffer[] = array($e);
		}	// *TABLES*
	}
	break;
/*-- END TOC --*/

/*-- INDEX --*/
     case 'INDEXENTRY':
	if (isset($attr['CONTENT']) && $attr['CONTENT']) {
		if (isset($attr['XREF']) && $attr['XREF']) {
			$this->IndexEntry(htmlspecialchars_decode($attr['CONTENT'],ENT_QUOTES),$attr['XREF']);
			break;
		}
		$objattr = array();
		$objattr['CONTENT'] = htmlspecialchars_decode($attr['CONTENT'],ENT_QUOTES);
		$objattr['type'] = 'indexentry';
		$e = "\xbb\xa4\xactype=indexentry,objattr=".serialize($objattr)."\xbb\xa4\xac";
		if($this->tableLevel) { $this->cell[$this->row][$this->col]['textbuffer'][] = array($e); } 	// *TABLES*
		else  {	// *TABLES*
			$this->textbuffer[] = array($e);
		}	// *TABLES*
	}
	break;

     
     case 'INDEXINSERT':
	if (isset($attr['FONT-SIZE'])) { $reffontsize = $attr['FONT-SIZE']; } else { $reffontsize = ''; }
	if (isset($attr['LINE-SPACING']) && $attr['LINE-SPACING']) { $linespacing = $attr['LINE-SPACING']; } else { $linespacing = ''; }
	if (isset($attr['DIV-FONT-SIZE']) && $attr['DIV-FONT-SIZE']) { $divlettfontsize = $attr['DIV-FONT-SIZE']; } else { $divlettfontsize = ''; }
	if (isset($attr['FONT']) && $attr['FONT']) { $reffont = $attr['FONT']; } else { $reffont = ''; }
	if (isset($attr['DIV-FONT']) && $attr['DIV-FONT']) { $divlettfont = $attr['DIV-FONT']; } else { $divlettfont = ''; }
	if (isset($attr['COLS']) && $attr['COLS']) { $cols = $attr['COLS']; } else { $cols = 1; }
	if (isset($attr['OFFSET']) && $attr['OFFSET']) { $offset = $attr['OFFSET']; } else { $offset = 3; }
	if (isset($attr['GAP']) && $attr['GAP']) { $gap = $attr['GAP']; } else { $gap = 5; }

	if (isset($attr['USEDIVLETTERS']) && (strtoupper($attr['USEDIVLETTERS'])=='OFF' || $attr['USEDIVLETTERS']==-1 || $attr['USEDIVLETTERS']==='0')) { $usedivletters = 0; }
	else { $usedivletters = 1; }

	if (isset($attr['LINKS']) && (strtoupper($attr['LINKS'])=='ON' || $attr['LINKS']==1)) { $links = true; }
	else { $links = false; }
	$this->CreateIndex($cols, $reffontsize, $linespacing, $offset, $usedivletters, $divlettfontsize, $gap, $reffont,$divlettfont, $links);
	break;
/*-- END INDEX --*/

/*-- WATERMARK --*/
     
     case 'WATERMARKTEXT':
	if (isset($attr['CONTENT']) && $attr['CONTENT']) { $txt = htmlspecialchars_decode($attr['CONTENT'],ENT_QUOTES); } else { $txt = ''; }
	if (isset($attr['ALPHA']) && $attr['ALPHA']>0) { $alpha = $attr['ALPHA']; } else { $alpha = -1; }
	$this->SetWatermarkText($txt, $alpha);
	break;

     
     case 'WATERMARKIMAGE':
	if (isset($attr['SRC'])) { $src = $attr['SRC']; } else { $src = ''; }
	if (isset($attr['ALPHA']) && $attr['ALPHA']>0) { $alpha = $attr['ALPHA']; } else { $alpha = -1; }
	if (isset($attr['SIZE']) && $attr['SIZE']) { 
		$size = $attr['SIZE']; 
		if (strpos($size,',')) { $size = explode(',',$size); }
	} 
	else { $size = 'D'; }
	if (isset($attr['POS']) && $attr['POS']) { 
		$pos = $attr['POS']; 
		if (strpos($pos,',')) { $pos = explode(',',$pos); }
	} 
	else { $pos = 'P'; }
	$this->SetWatermarkImage($src, $alpha, $size, $pos);
	break;
/*-- END WATERMARK --*/

/*-- BOOKMARKS --*/
     case 'BOOKMARK':
	if (isset($attr['CONTENT'])) {
		$objattr = array();
		$objattr['CONTENT'] = htmlspecialchars_decode($attr['CONTENT'],ENT_QUOTES);
		$objattr['type'] = 'bookmark';
		if (isset($attr['LEVEL']) && $attr['LEVEL']) { $objattr['bklevel'] = $attr['LEVEL']; } else { $objattr['bklevel'] = 0; }
		$e = "\xbb\xa4\xactype=bookmark,objattr=".serialize($objattr)."\xbb\xa4\xac";
		if($this->tableLevel) { $this->cell[$this->row][$this->col]['textbuffer'][] = array($e); }	// *TABLES*
		else  {	// *TABLES*
			$this->textbuffer[] = array($e);
		}	// *TABLES*
	}
	break;
/*-- END BOOKMARKS --*/

/*-- ANNOTATIONS --*/
     case 'ANNOTATION':

	//if (isset($attr['CONTENT']) && !$this->writingHTMLheader && !$this->writingHTMLfooter) {	// Stops annotations in FixedPos
	if (isset($attr['CONTENT'])) {
		$objattr = array();
		$objattr['margin_top'] = 0;
		$objattr['margin_bottom'] = 0;
		$objattr['margin_left'] = 0;
		$objattr['margin_right'] = 0;
		$objattr['width'] = 0;
		$objattr['height'] = 0;
		$objattr['border_top']['w'] = 0;
		$objattr['border_bottom']['w'] = 0;
		$objattr['border_left']['w'] = 0;
		$objattr['border_right']['w'] = 0;
		$objattr['CONTENT'] = htmlspecialchars_decode($attr['CONTENT'],ENT_QUOTES);
		$objattr['type'] = 'annot';
		$objattr['POPUP'] = ''; 
	}
	else { break; }
	if (isset($attr['POS-X'])) { $objattr['POS-X'] = $attr['POS-X']; } else { $objattr['POS-X'] = 0; }
	if (isset($attr['POS-Y'])) { $objattr['POS-Y'] = $attr['POS-Y']; } else { $objattr['POS-Y'] = 0; }
	if (isset($attr['ICON'])) { $objattr['ICON'] = $attr['ICON']; } else { $objattr['ICON'] = 'Note'; }
	if (isset($attr['AUTHOR'])) { $objattr['AUTHOR'] = $attr['AUTHOR']; } 
	else if (isset($attr['TITLE'])) { $objattr['AUTHOR'] = $attr['TITLE']; } else  { $objattr['AUTHOR'] = ''; }
	if (isset($attr['FILE'])) { $objattr['FILE'] = $attr['FILE']; } else  { $objattr['FILE'] = ''; }
	if (isset($attr['SUBJECT'])) { $objattr['SUBJECT'] = $attr['SUBJECT']; } else  { $objattr['SUBJECT'] = ''; }
	if (isset($attr['OPACITY']) && $attr['OPACITY']>0 && $attr['OPACITY']<=1) { $objattr['OPACITY'] = $attr['OPACITY']; } 
	else if ($this->annotMargin) { $objattr['OPACITY'] = 1; }
	else { $objattr['OPACITY'] = $this->annotOpacity; }
	if (isset($attr['COLOR'])) { 
		$cor = $this->ConvertColor($attr['COLOR']);
		if ($cor) {  $objattr['COLOR'] = $cor; }
		else  { $objattr['COLOR'] = $this->ConvertColor('yellow'); }
	} 
	else  { $objattr['COLOR'] = $this->ConvertColor('yellow'); }

	if (isset($attr['POPUP']) && !empty($attr['POPUP'])) { 
		$pop = preg_split('/\s+/',trim($attr['POPUP']));
		if (count($pop)>1) { $objattr['POPUP'] = $pop; }
		else { $objattr['POPUP'] = true; }
	} 
	$e = "\xbb\xa4\xactype=annot,objattr=".serialize($objattr)."\xbb\xa4\xac";
	if($this->tableLevel) { $this->cell[$this->row][$this->col]['textbuffer'][] = array($e); }	// *TABLES*
	else  {	// *TABLES*
		$this->textbuffer[] = array($e);
	}	// *TABLES*
	break;
/*-- END ANNOTATIONS --*/


/*-- COLUMNS --*/
    case 'COLUMNS': //added custom-tag
	if (isset($attr['COLUMN-COUNT']) && ($attr['COLUMN-COUNT'] || $attr['COLUMN-COUNT']==='0')) {
		// Close any open block tags
		for ($b= $this->blklvl;$b>0;$b--) { $this->CloseTag($this->blk[$b]['tag']); }
		if(!empty($this->textbuffer))  {	//Output previously buffered content
    		  	$this->printbuffer($this->textbuffer);
      	  	$this->textbuffer=array(); 
      	}

		if (isset($attr['VALIGN']) && $attr['VALIGN']) { 
			if ($attr['VALIGN'] == 'J') { $valign = 'J'; }
			else { $valign = $align[$attr['VALIGN']]; }
		}
 		else { $valign = ''; }
		if (isset($attr['COLUMN-GAP']) && $attr['COLUMN-GAP']) { $this->SetColumns($attr['COLUMN-COUNT'],$valign,$attr['COLUMN-GAP']); }
		else { $this->SetColumns($attr['COLUMN-COUNT'],$valign); }
	}
	$this->ignorefollowingspaces = true;
	break;

    case 'COLUMN_BREAK': //custom-tag
    case 'COLUMNBREAK': //custom-tag
    case 'NEWCOLUMN': //custom-tag
	$this->ignorefollowingspaces = true;
	$this->NewColumn();
	$this->ColumnAdjust = false;	// disables all column height adjustment for the page.
	break;

/*-- END COLUMNS --*/


    case 'BDO':
	// $this->biDirectional = true;
	break;


    case 'TTZ':
	$this->ttz = true;
	$this->InlineProperties[$tag] = $this->saveInlineProperties();
	$this->setCSS(array('FONT-FAMILY'=>'czapfdingbats','FONT-WEIGHT'=>'normal','FONT-STYLE'=>'normal'),'INLINE');
	break;

    case 'TTS':
	$this->tts = true;
	$this->InlineProperties[$tag] = $this->saveInlineProperties();
	$this->setCSS(array('FONT-FAMILY'=>'csymbol','FONT-WEIGHT'=>'normal','FONT-STYLE'=>'normal'),'INLINE');
	break;

    case 'TTA':
	$this->tta = true;
	$this->InlineProperties[$tag] = $this->saveInlineProperties();

	if (in_array($this->FontFamily,$this->mono_fonts)) {
		$this->setCSS(array('FONT-FAMILY'=>'ccourier'),'INLINE');
	}
	else if (in_array($this->FontFamily,$this->serif_fonts)) { 
		$this->setCSS(array('FONT-FAMILY'=>'ctimes'),'INLINE');
	}
	else {
		$this->setCSS(array('FONT-FAMILY'=>'chelvetica'),'INLINE');
	}
	break;



    // INLINE PHRASES OR STYLES
    case 'SUB':
    case 'SUP':
    case 'ACRONYM':
    case 'BIG':
    case 'SMALL':
    case 'INS':
    case 'S':
    case 'STRIKE':
    case 'DEL':
    case 'STRONG':
    case 'CITE':
    case 'Q':
    case 'EM':
    case 'B':
    case 'I':
    case 'U':
    case 'SAMP':
    case 'CODE':
    case 'KBD':
    case 'TT':
    case 'VAR':
    case 'FONT':
    case 'MARK':	// mPDF 5.5.09
    case 'TIME':

    case 'SPAN':
/*-- ANNOTATIONS --*/
	if ($this->title2annots && isset($attr['TITLE'])) {
		$objattr = array();
		$objattr['margin_top'] = 0;
		$objattr['margin_bottom'] = 0;
		$objattr['margin_left'] = 0;
		$objattr['margin_right'] = 0;
		$objattr['width'] = 0;
		$objattr['height'] = 0;
		$objattr['border_top']['w'] = 0;
		$objattr['border_bottom']['w'] = 0;
		$objattr['border_left']['w'] = 0;
		$objattr['border_right']['w'] = 0;

		$objattr['CONTENT'] = $attr['TITLE'];
		$objattr['type'] = 'annot';
		$objattr['POS-X'] = 0;
		$objattr['POS-Y'] = 0;
		$objattr['ICON'] = 'Comment';
		$objattr['AUTHOR'] = '';
		$objattr['SUBJECT'] = '';
		$objattr['OPACITY'] = $this->annotOpacity; 
		$objattr['COLOR'] = $this->ConvertColor('yellow'); 
		$annot = "\xbb\xa4\xactype=annot,objattr=".serialize($objattr)."\xbb\xa4\xac";
	}
/*-- END ANNOTATIONS --*/

	if ($tag == 'SPAN') {
		$this->spanlvl++;
		$this->InlineProperties['SPAN'][$this->spanlvl] = $this->saveInlineProperties();
		if (isset($annot)) { $this->InlineAnnots[$tag][$this->spanlvl] = $annot; }	// *ANNOTATIONS*
	}
	else { 
		if (!isset($this->InlineProperties[$tag])) $this->InlineProperties[$tag] = $this->saveInlineProperties(); // mPDF 5.4.13
		if (isset($annot)) { $this->InlineAnnots[$tag] = $annot; }	// *ANNOTATIONS*
	}
	$properties = $this->cssmgr->MergeCSS('INLINE',$tag,$attr);
	if (!empty($properties)) $this->setCSS($properties,'INLINE');
	break;


    case 'A':
	if (isset($attr['NAME']) and $attr['NAME'] != '') { 
		$e = '';
/*-- BOOKMARKS --*/
		if ($this->anchor2Bookmark) { 
			$objattr = array();
			$objattr['CONTENT'] = htmlspecialchars_decode($attr['NAME'],ENT_QUOTES);
			$objattr['type'] = 'bookmark';
			if (isset($attr['LEVEL']) && $attr['LEVEL']) { $objattr['bklevel'] = $attr['LEVEL']; } else { $objattr['bklevel'] = 0; }
			$e = "\xbb\xa4\xactype=bookmark,objattr=".serialize($objattr)."\xbb\xa4\xac";
		}
/*-- END BOOKMARKS --*/
		if($this->tableLevel) {	// *TABLES*
			$this->_saveCellTextBuffer($e, '', $attr['NAME']);	// *TABLES*
		}	// *TABLES*
		else  {	// *TABLES*
			$this->_saveTextBuffer($e, '', $attr['NAME']);	//an internal link (adds a space for recognition)
		}	// *TABLES*
	}
	if (isset($attr['HREF'])) { 
		$this->InlineProperties['A'] = $this->saveInlineProperties();
		$properties = $this->cssmgr->MergeCSS('',$tag,$attr);
		if (!empty($properties)) $this->setCSS($properties,'INLINE');
		$this->HREF=htmlspecialchars_decode(urldecode($attr['HREF']));
	}
	break;

    case 'LEGEND':	// mPDF 5.4.18
		$this->InlineProperties['LEGEND'] = $this->saveInlineProperties();
		$properties = $this->cssmgr->MergeCSS('',$tag,$attr);
		if (!empty($properties)) $this->setCSS($properties,'INLINE');
	break;



    case 'PROGRESS':	// mPDF 5.5.09
    case 'METER':	// mPDF 5.5.09
	$this->inMeter = true;	// mPDF 5.5.09

	if (isset($attr['MAX']) && $attr['MAX']) { $max = $attr['MAX']; }
	else { $max = 1; }
	if (isset($attr['MIN']) && $attr['MIN'] && $tag=='METER') { $min = $attr['MIN']; }
	else { $min = 0; }
	if ($max < $min) { $max = $min; }

	if (isset($attr['VALUE']) && ($attr['VALUE'] || $attr['VALUE']==='0')) {
		$value = $attr['VALUE']; 
		if ($value < $min) { $value = $min; }
		else if ($value > $max) { $value = $max; }
	}
	else { $value = ''; }

	if (isset($attr['LOW']) && $attr['LOW']) { $low = $attr['LOW']; }
	else { $low = $min; }
	if ($low < $min) { $low = $min; }
	else if ($low > $max) { $low = $max; }
	if (isset($attr['HIGH']) && $attr['HIGH']) { $high = $attr['HIGH']; }
	else { $high = $max; }
	if ($high < $low) { $high = $low; }
	else if ($high > $max) { $high = $max; }
	if (isset($attr['OPTIMUM']) && $attr['OPTIMUM']) { $optimum = $attr['OPTIMUM']; }
	else { $optimum = $min + (($max-$min)/2); }
	if ($optimum < $min) { $optimum = $min; }
	else if ($optimum > $max) { $optimum = $max; }
	if (isset($attr['TYPE']) && $attr['TYPE']) { $type = $attr['TYPE']; }
	else { $type = ''; }
	$objattr = array();
		$objattr['margin_top'] = 0;
		$objattr['margin_bottom'] = 0;
		$objattr['margin_left'] = 0;
		$objattr['margin_right'] = 0;
		$objattr['padding_top'] = 0;
		$objattr['padding_bottom'] = 0;
		$objattr['padding_left'] = 0;
		$objattr['padding_right'] = 0;
		$objattr['width'] = 0;
		$objattr['height'] = 0;
		$objattr['border_top']['w'] = 0;
		$objattr['border_bottom']['w'] = 0;
		$objattr['border_left']['w'] = 0;
		$objattr['border_right']['w'] = 0;

		$properties = $this->cssmgr->MergeCSS('',$tag,$attr);
		if(isset($properties ['DISPLAY']) && strtolower($properties ['DISPLAY'])=='none') { 
			return; 
		}
		$objattr['visibility'] = 'visible'; 
		if (isset($properties['VISIBILITY'])) {
			$v = strtolower($properties['VISIBILITY']);
			if (($v == 'hidden' || $v == 'printonly' || $v == 'screenonly') && $this->visibility=='visible') { 
				$objattr['visibility'] = $v; 
			}
		}

		if (isset($properties['MARGIN-TOP'])) { $objattr['margin_top']=$this->ConvertSize($properties['MARGIN-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if (isset($properties['MARGIN-BOTTOM'])) { $objattr['margin_bottom'] = $this->ConvertSize($properties['MARGIN-BOTTOM'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if (isset($properties['MARGIN-LEFT'])) { $objattr['margin_left'] = $this->ConvertSize($properties['MARGIN-LEFT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if (isset($properties['MARGIN-RIGHT'])) { $objattr['margin_right'] = $this->ConvertSize($properties['MARGIN-RIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }

		if (isset($properties['PADDING-TOP'])) { $objattr['padding_top']=$this->ConvertSize($properties['PADDING-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if (isset($properties['PADDING-BOTTOM'])) { $objattr['padding_bottom'] = $this->ConvertSize($properties['PADDING-BOTTOM'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if (isset($properties['PADDING-LEFT'])) { $objattr['padding_left'] = $this->ConvertSize($properties['PADDING-LEFT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if (isset($properties['PADDING-RIGHT'])) { $objattr['padding_right'] = $this->ConvertSize($properties['PADDING-RIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }

		if (isset($properties['BORDER-TOP'])) { $objattr['border_top'] = $this->border_details($properties['BORDER-TOP']); }
		if (isset($properties['BORDER-BOTTOM'])) { $objattr['border_bottom'] = $this->border_details($properties['BORDER-BOTTOM']); }
		if (isset($properties['BORDER-LEFT'])) { $objattr['border_left'] = $this->border_details($properties['BORDER-LEFT']); }
		if (isset($properties['BORDER-RIGHT'])) { $objattr['border_right'] = $this->border_details($properties['BORDER-RIGHT']); }

		if (isset($properties['VERTICAL-ALIGN'])) { $objattr['vertical-align'] = $align[strtolower($properties['VERTICAL-ALIGN'])]; }
		$w = 0;
		$h = 0;
		if(isset($properties['WIDTH'])) $w = $this->ConvertSize($properties['WIDTH'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
		else if(isset($attr['WIDTH'])) $w = $this->ConvertSize($attr['WIDTH'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);

		if(isset($properties['HEIGHT'])) $h = $this->ConvertSize($properties['HEIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
		else if(isset($attr['HEIGHT'])) $h = $this->ConvertSize($attr['HEIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);

		if (isset($properties['OPACITY']) && $properties['OPACITY'] > 0 && $properties['OPACITY'] <= 1) { $objattr['opacity'] = $properties['OPACITY']; }
		if ($this->HREF) {
			if (strpos($this->HREF,".") === false && strpos($this->HREF,"@") !== 0) {
				$href = $this->HREF;
				while(array_key_exists($href,$this->internallink)) $href="#".$href;
	    			$this->internallink[$href] = $this->AddLink();
				$objattr['link'] = $this->internallink[$href];
			}
			else { $objattr['link'] = $this->HREF; }
		}
		$extraheight = $objattr['padding_top'] + $objattr['padding_bottom'] + $objattr['margin_top'] + $objattr['margin_bottom'] + $objattr['border_top']['w'] + $objattr['border_bottom']['w'];
		$extrawidth = $objattr['padding_left'] + $objattr['padding_right'] + $objattr['margin_left'] + $objattr['margin_right'] + $objattr['border_left']['w'] + $objattr['border_right']['w'];

		// Image file
		if (!class_exists('meter', false)) { 
			include(_MPDF_PATH.'classes/meter.php'); 
		}
		$this->meter = new meter();
		$svg = $this->meter->makeSVG(strtolower($tag), $type, $value, $max, $min, $optimum, $low, $high);
		//Save to local file
		$srcpath= _MPDF_TEMP_PATH.'_tempSVG'.RAND(1,10000).'_'.strtolower($tag).'.svg';
		file_put_contents($srcpath, $svg);
		$orig_srcpath = $srcpath;
		$this->GetFullPath($srcpath); 

		$info=$this->_getImage($srcpath, true, true, $orig_srcpath);
		if(!$info) {
			$info = $this->_getImage($this->noImageFile);
			if ($info) { 
				$srcpath = $this->noImageFile; 
				$w = ($info['w'] * (25.4/$this->dpi)); 
				$h = ($info['h'] * (25.4/$this->dpi));
			}
		}
		if(!$info) break;

		$objattr['file'] = $srcpath;
		//Default width and height calculation if needed
		if($w==0 and $h==0) {
			// SVG units are pixels
			$w = $this->FontSize/(10/_MPDFK) * abs($info['w'])/_MPDFK;	// mPDF 5.5.21
			$h = $this->FontSize/(10/_MPDFK) * abs($info['h'])/_MPDFK;
		}
		// IF WIDTH OR HEIGHT SPECIFIED
		if($w==0)  $w=abs($h*$info['w']/$info['h']); 
		if($h==0)	$h=abs($w*$info['h']/$info['w']);

		// Resize to maximum dimensions of page
		$maxWidth = $this->blk[$this->blklvl]['inner_width'];
   		$maxHeight = $this->h - ($this->tMargin + $this->bMargin + 1) ;
		if ($this->fullImageHeight) { $maxHeight = $this->fullImageHeight; }
		if ($w + $extrawidth > $maxWidth ) {
			$w = $maxWidth - $extrawidth;
			$h=abs($w*$info['h']/$info['w']);
		}

		if ($h + $extraheight > $maxHeight ) {
			$h = $maxHeight - $extraheight;
			$w=abs($h*$info['w']/$info['h']);
		}
		$objattr['type'] = 'image';
		$objattr['itype'] = $info['type'];

		$objattr['orig_h'] = $info['h'];
		$objattr['orig_w'] = $info['w'];
		$objattr['wmf_x'] = $info['x'];
		$objattr['wmf_y'] = $info['y'];
		$objattr['height'] = $h + $extraheight;
		$objattr['width'] = $w + $extrawidth;
		$objattr['image_height'] = $h;
		$objattr['image_width'] = $w;
		$e = "\xbb\xa4\xactype=image,objattr=".serialize($objattr)."\xbb\xa4\xac";
		$properties = array();
		if ($this->tableLevel) {
			$this->_saveCellTextBuffer($e, $this->HREF);
			$this->cell[$this->row][$this->col]['s'] += $objattr['width'] ;
		}
		else {
			$this->_saveTextBuffer($e, $this->HREF);
		}

	break;


    case 'BR':
	// Added mPDF 3.0 Float DIV - CLEAR
	if (isset($attr['STYLE'])) {
		$properties = $this->cssmgr->readInlineCSS($attr['STYLE']);
		if (isset($properties['CLEAR'])) { $this->ClearFloats(strtoupper($properties['CLEAR']),$this->blklvl); }	// *CSS-FLOAT*
	}


/*-- TABLES --*/
	if($this->tableLevel) {
	   
	   if ($this->blockjustfinished || $this->listjustfinished) {
		$this->_saveCellTextBuffer("\n");
	   }

		$this->_saveCellTextBuffer("\n");
		if (!isset($this->cell[$this->row][$this->col]['maxs'])) {
			$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s']; 
		}
		elseif($this->cell[$this->row][$this->col]['maxs'] < $this->cell[$this->row][$this->col]['s']) {
			$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s'];  
		}
		$this->cell[$this->row][$this->col]['s'] = 0 ;// reset
	}
	else  {
/*-- END TABLES --*/
		if (count($this->textbuffer)) {
			$this->textbuffer[count($this->textbuffer)-1][0] = preg_replace('/ $/','',$this->textbuffer[count($this->textbuffer)-1][0]);
		}
		$this->_saveTextBuffer("\n");
	}	// *TABLES*
	$this->ignorefollowingspaces = true; 
	$this->blockjustfinished=false;
	$this->listjustfinished=false;

	$this->linebreakjustfinished=true;
	break;


	// *********** BLOCKS  ********************


    case 'PRE':
	$this->ispre=true;	// ADDED - Prevents left trim of textbuffer in printbuffer()

    case 'DIV':
    case 'FORM':
    case 'CENTER':

    case 'BLOCKQUOTE':
    case 'ADDRESS': 

    case 'CAPTION':
    case 'P':
    case 'H1':
    case 'H2':
    case 'H3':
    case 'H4':
    case 'H5':
    case 'H6':
    case 'DL':
    case 'DT':
    case 'DD':
    case 'FIELDSET':
    // mPDF 5.5.22
    case 'DETAILS':
    case 'SUMMARY':
    // mPDF 5.5.09
    case 'ARTICLE':
    case 'ASIDE':
    case 'FIGURE':
    case 'FIGCAPTION':
    case 'FOOTER':
    case 'HEADER':
    case 'HGROUP':
    case 'NAV':
    case 'SECTION':
	$p = $this->cssmgr->PreviewBlockCSS($tag,$attr);
	if(isset($p['DISPLAY']) && strtolower($p['DISPLAY'])=='none') { 
		$this->blklvl++;
		$this->blk[$this->blklvl]['hide'] = true; 
		return; 
	}
	if($tag == 'CAPTION') {
		// position is written in AdjstHTML
		if (isset($attr['POSITION']) && strtolower($attr['POSITION'])=='bottom') { $divpos = 'B'; }
		else { $divpos = 'T'; }
		if (isset($attr['ALIGN']) && strtolower($attr['ALIGN'])=='bottom') { $cappos = 'B'; }
		else if (isset($p['CAPTION-SIDE']) && strtolower($p['CAPTION-SIDE'])=='bottom') { $cappos = 'B'; }
		else { $cappos = 'T'; }
		if (isset($attr['ALIGN'])) { unset($attr['ALIGN']); }
		if ($cappos != $divpos) {
			$this->blklvl++;
			$this->blk[$this->blklvl]['hide'] = true; 
			return; 
		}
	}

/*-- FORMS --*/
	if($tag == 'FORM') {
		if (isset($attr['METHOD']) && strtolower($attr['METHOD'])=='get') { $this->form->formMethod = 'GET'; }
		else { $this->form->formMethod = 'POST'; }
		if (isset($attr['ACTION'])) { $this->form->formAction = $attr['ACTION']; }
		else { $this->form->formAction = ''; }
	}
/*-- END FORMS --*/


/*-- CSS-POSITION --*/
	if ((isset($p['POSITION']) && (strtolower($p['POSITION'])=='fixed' || strtolower($p['POSITION'])=='absolute')) && $this->blklvl==0) {
		if ($this->inFixedPosBlock) {
			$this->Error("Cannot nest block with position:fixed or position:absolute"); 
		}
		$this->inFixedPosBlock = true;
		return;
	}
/*-- END CSS-POSITION --*/
	// Start Block
	$this->ignorefollowingspaces = true; 

	if ($this->blockjustfinished && !count($this->textbuffer) && $this->y != $this->tMargin && $this->collapseBlockMargins) { $lastbottommargin = $this->lastblockbottommargin; }
	else { $lastbottommargin = 0; }
	$this->lastblockbottommargin = 0;
	$this->blockjustfinished=false;

/*-- LISTS --*/
	if ($this->listlvl>0) { return; }
/*-- END LISTS --*/

	$this->InlineProperties = array(); 
	$this->spanlvl = 0;
	$this->listjustfinished=false;
	$this->divbegin=true;

	$this->linebreakjustfinished=false;

/*-- TABLES --*/
	if ($this->tableLevel) {
	   
	   // If already something on the line
	   if ($this->cell[$this->row][$this->col]['s'] > 0  && !$this->nestedtablejustfinished ) {
		$this->_saveCellTextBuffer("\n");
		if (!isset($this->cell[$this->row][$this->col]['maxs'])) {
			$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s'];
		}
		elseif($this->cell[$this->row][$this->col]['maxs'] < $this->cell[$this->row][$this->col]['s']) {
			$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s']; 
		}
		$this->cell[$this->row][$this->col]['s'] = 0 ;// reset
	   }
	   // Cannot set block properties inside table - use Bold to indicate h1-h6
	   if ($tag == 'CENTER' && $this->tdbegin) { $this->cell[$this->row][$this->col]['a'] = $align['center']; }

		$this->InlineProperties['BLOCKINTABLE'] = $this->saveInlineProperties();
		$properties = $this->cssmgr->MergeCSS('',$tag,$attr);
		if (!empty($properties)) $this->setCSS($properties,'INLINE');


	   break;
	}
/*-- END TABLES --*/

	if ($tag == 'P' || $tag == 'DT' || $tag == 'DD') { $this->lastoptionaltag = $tag; } // Save current HTML specified optional endtag
	else { $this->lastoptionaltag = ''; }

	if ($this->lastblocklevelchange == 1) { $blockstate = 1; }	// Top margins/padding only
	else if ($this->lastblocklevelchange < 1) { $blockstate = 0; }	// NO margins/padding
	$this->printbuffer($this->textbuffer,$blockstate);
	$this->textbuffer=array();

	$save_blklvl = $this->blklvl;
	$save_blk = $this->blk;
	$save_silp = $this->saveInlineProperties();
	$save_spanlvl = $this->spanlvl;
	$save_ilp = $this->InlineProperties;

	$this->blklvl++;

	$currblk =& $this->blk[$this->blklvl];
	$this->initialiseBlock($currblk);
	$prevblk =& $this->blk[$this->blklvl-1];

	$currblk['tag'] = $tag;
	$currblk['attr'] = $attr;

	$this->Reset();
	$properties = $this->cssmgr->MergeCSS('BLOCK',$tag,$attr);
	$pagesel = ''; 
/*-- CSS-PAGE --*/

	if (isset($properties['PAGE'])) { $pagesel = $properties['PAGE']; } 
/*-- END CSS-PAGE --*/

	// If page-box has changed AND/OR PAGE-BREAK-BEFORE
	$save_cols = false;
	if (($pagesel && $pagesel != $this->page_box['current']) || (isset($properties['PAGE-BREAK-BEFORE']) && $properties['PAGE-BREAK-BEFORE'])) {
		if ($this->blklvl>1) {
			// Close any open block tags
			for ($b= $this->blklvl;$b>0;$b--) { $this->CloseTag($this->blk[$b]['tag']); }
			// Output any text left in buffer
			if (count($this->textbuffer)) { $this->printbuffer($this->textbuffer); $this->textbuffer=array(); }
		}
/*-- COLUMNS --*/
		if ($this->ColActive) {
			$save_cols = true;
			$save_nbcol = $this->NbCol;	// other values of gap and vAlign will not change by setting Columns off
			$this->SetColumns(0);
		}
/*-- END COLUMNS --*/


		// Must Add new page if changed page properties
		if (isset($properties['PAGE-BREAK-BEFORE'])) {
			if (strtoupper($properties['PAGE-BREAK-BEFORE']) == 'RIGHT') { $this->AddPage($this->CurOrientation,'NEXT-ODD','','','','','', '','', '','','','','','',0,0,0,0,$pagesel); }
			else if (strtoupper($properties['PAGE-BREAK-BEFORE']) == 'LEFT') { $this->AddPage($this->CurOrientation,'NEXT-EVEN','','','','','', '','', '','','','','','',0,0,0,0,$pagesel); }
			else if (strtoupper($properties['PAGE-BREAK-BEFORE']) == 'ALWAYS') { $this->AddPage($this->CurOrientation,'','','','','','', '','', '','','','','','',0,0,0,0,$pagesel); }
			else if ($this->page_box['current'] != $pagesel) { $this->AddPage($this->CurOrientation,'','','','','','', '','', '','','','','','',0,0,0,0,$pagesel); }	// *CSS-PAGE*
		}
/*-- CSS-PAGE --*/
		else if ($pagesel != $this->page_box['current']) { $this->AddPage($this->CurOrientation,'','','','','','', '','', '','','','','','',0,0,0,0,$pagesel); }
/*-- END CSS-PAGE --*/

		// if using htmlheaders, the headers need to be rewritten when new page
		// done by calling WriteHTML() within resethtmlheaders
		// so block is reset to 0 - now we need to resurrect it
		// As in WriteHTML() initialising
		if (!($this->restoreBlockPagebreaks && isset($properties['PAGE-BREAK-BEFORE']) && $properties['PAGE-BREAK-BEFORE'])) {
			$this->blklvl = 0;
			$this->lastblocklevelchange = 0;
			$this->blk = array();
			$this->initialiseBlock($this->blk[0]);
			$this->blk[0]['width'] =& $this->pgwidth;
			$this->blk[0]['inner_width'] =& $this->pgwidth;
			$this->blk[0]['blockContext'] = $this->blockContext;
			$properties = $this->cssmgr->MergeCSS('BLOCK','BODY','');
			$this->setCSS($properties,'','BODY'); 
			$this->blklvl++;
			$currblk =& $this->blk[$this->blklvl];
			$prevblk =& $this->blk[$this->blklvl-1];

			$this->initialiseBlock($currblk);
			$currblk['tag'] = $tag;
			$currblk['attr'] = $attr;

			$this->Reset();
			$properties = $this->cssmgr->MergeCSS('BLOCK',$tag,$attr);
		}
/*-- COLUMNS --*/
		if ($save_cols) {
			// Restore columns
			$this->SetColumns($save_nbcol,$this->colvAlign,$this->ColGap);
		}
/*-- END COLUMNS --*/
		if ($this->restoreBlockPagebreaks && isset($properties['PAGE-BREAK-BEFORE']) && $properties['PAGE-BREAK-BEFORE']) {
			$this->blk = $save_blk;
			// Re-open block tags
			$t = $this->blk[0]['tag'];
			$a = $this->blk[0]['attr'];
			$this->blklvl = 0; 
			for ($b=0; $b<=$save_blklvl;$b++) {
				$tc = $t;
				$ac = $a;
				$t = $this->blk[$b+1]['tag'];
				$a = $this->blk[$b+1]['attr'];
				unset($this->blk[$b+1]);
				$this->OpenTag($tc,$ac); 
			}
			$this->spanlvl = $save_spanlvl;
			$this->InlineProperties = $save_ilp;
			$this->restoreInlineProperties($save_silp);
		}
	}

	if (isset($properties['PAGE-BREAK-INSIDE']) && strtoupper($properties['PAGE-BREAK-INSIDE']) == 'AVOID' && !$this->ColActive && !$this->keep_block_together) {
		$currblk['keep_block_together'] = 1;
		$this->kt_y00 = $this->y;
		$this->kt_p00 = $this->page;
		$this->keep_block_together = 1;
		$this->divbuffer = array();
		$this->ktLinks = array();
		$this->ktAnnots = array();
		$this->ktForms = array();
		$this->ktBlock = array();
		$this->ktReference = array();
		$this->ktBMoutlines = array();
		$this->_kttoc = array();
	}
	if ($lastbottommargin && isset($properties['MARGIN-TOP']) && $properties['MARGIN-TOP'] && empty($properties['FLOAT'])) { $currblk['lastbottommargin'] = $lastbottommargin; }

	// mPDF 5.6.01  - LAYERS
	if (isset($properties['Z-INDEX']) && $this->currentlayer==0) {
		$v = intval($properties['Z-INDEX']); 
		if ($v > 0) {
			$currblk['z-index'] = $v; 
			$this->BeginLayer($v);
		}
	}

	$this->setCSS($properties,'BLOCK',$tag); //name(id/class/style) found in the CSS array!
	$currblk['InlineProperties'] = $this->saveInlineProperties();

	if (isset($properties['VISIBILITY'])) {
		$v = strtolower($properties['VISIBILITY']);
		if (($v == 'hidden' || $v == 'printonly' || $v == 'screenonly') && $this->visibility=='visible' && !$this->tableLevel) { 
			$currblk['visibility'] = $v; 
			$this->SetVisibility($v);
		}
	}

	if(isset($attr['DIR']) && $attr['DIR']) { $currblk['direction'] = strtolower($attr['DIR']); }
	if(isset($attr['ALIGN']) && $attr['ALIGN']) { $currblk['block-align'] = $align[strtolower($attr['ALIGN'])]; }

	if (isset($properties['HEIGHT'])) { 
		$currblk['css_set_height'] = $this->ConvertSize($properties['HEIGHT'],($this->h - $this->tMargin - $this->bMargin),$this->FontSize,false); 
		if (($currblk['css_set_height'] + $this->y) > $this->PageBreakTrigger && $this->y > $this->tMargin+5 && $currblk['css_set_height'] < ($this->h - ($this->tMargin + $this->bMargin))) { $this->AddPage($this->CurOrientation); }
	}
	else { $currblk['css_set_height'] = false; }


	// Added mPDF 3.0 Float DIV
	if (isset($prevblk['blockContext'])) { $currblk['blockContext'] = $prevblk['blockContext'] ; }	// *CSS-FLOAT*

	if (isset($properties['CLEAR'])) { $this->ClearFloats(strtoupper($properties['CLEAR']), $this->blklvl-1); }	// *CSS-FLOAT*

	$container_w = $prevblk['inner_width'];
	$bdr = $currblk['border_right']['w'];
	$bdl = $currblk['border_left']['w'];
	$pdr = $currblk['padding_right'];
	$pdl = $currblk['padding_left'];

	if (isset($currblk['css_set_width'])) { $setwidth = $currblk['css_set_width']; }
	else { $setwidth = 0; }

/*-- CSS-FLOAT --*/
	if (isset($properties['FLOAT']) && strtoupper($properties['FLOAT']) == 'RIGHT' && !$this->ColActive) {
		// Cancel Keep-Block-together
		$currblk['keep_block_together'] = false;
		$this->kt_y00 = '';
		$this->keep_block_together = 0;

		$this->blockContext++;
		$currblk['blockContext'] = $this->blockContext;

		list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl-1);

		// DIV is too narrow for text to fit!
		$maxw = $container_w - $l_width - $r_width;
		if (($setwidth + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr) > $maxw || ($maxw - ($currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr)) < (2*$this->GetCharWidth('W',false))) {
			// Too narrow to fit - try to move down past L or R float
			if ($l_max < $r_max && ($setwidth + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr) <= ($container_w - $r_width) && (($container_w - $r_width) - ($currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr)) > (2*$this->GetCharWidth('W',false))) {
				$this->ClearFloats('LEFT', $this->blklvl-1); 
			}
			else if ($r_max < $l_max && ($setwidth + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr)  <= ($container_w - $l_width) && (($container_w - $l_width) - ($currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr)) > (2*$this->GetCharWidth('W',false))) {
				$this->ClearFloats('RIGHT', $this->blklvl-1); 
			}
			else { $this->ClearFloats('BOTH', $this->blklvl-1); }
			list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl-1);
		}

		if ($r_exists) { $currblk['margin_right'] += $r_width; }

		$currblk['float'] = 'R';
		$currblk['float_start_y'] = $this->y;
		if ($currblk['css_set_width']) {
			$currblk['margin_left'] = $container_w - ($setwidth + $bdl + $pdl + $bdr + $pdr + $currblk['margin_right']);
			$currblk['float_width'] = ($setwidth + $bdl + $pdl + $bdr + $pdr + $currblk['margin_right']);
		}
		else {
			// *** If no width set - would need to buffer and keep track of max width, then Right-align if not full width
			// and do borders and backgrounds - For now - just set to maximum width left

			if ($l_exists) { $currblk['margin_left'] += $l_width; }
			$currblk['css_set_width'] = $container_w - ($currblk['margin_left'] + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr);

			$currblk['float_width'] = ($currblk['css_set_width'] + $bdl + $pdl + $bdr + $pdr + $currblk['margin_right']);
		}
	}
	else if (isset($properties['FLOAT']) && strtoupper($properties['FLOAT']) == 'LEFT' && !$this->ColActive) {
		// Cancel Keep-Block-together
		$currblk['keep_block_together'] = false;
		$this->kt_y00 = '';
		$this->keep_block_together = 0;

		$this->blockContext++;
		$currblk['blockContext'] = $this->blockContext;

		list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl-1);

		// DIV is too narrow for text to fit!
		$maxw = $container_w - $l_width - $r_width;
		if (($setwidth + $currblk['margin_left'] + $bdl + $pdl + $bdr + $pdr) > $maxw || ($maxw - ($currblk['margin_left'] + $bdl + $pdl + $bdr + $pdr)) < (2*$this->GetCharWidth('W',false))) {
			// Too narrow to fit - try to move down past L or R float
			if ($l_max < $r_max && ($setwidth + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr) <= ($container_w - $r_width) && (($container_w - $r_width) - ($currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr)) > (2*$this->GetCharWidth('W',false))) {
				$this->ClearFloats('LEFT', $this->blklvl-1); 
			}
			else if ($r_max < $l_max && ($setwidth + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr) <= ($container_w - $l_width) && (($container_w - $l_width) - ($currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr)) > (2*$this->GetCharWidth('W',false))) {
				$this->ClearFloats('RIGHT', $this->blklvl-1); 
			}
			else { $this->ClearFloats('BOTH', $this->blklvl-1); }
			list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl-1);
		}

		if ($l_exists) { $currblk['margin_left'] += $l_width; }

		$currblk['float'] = 'L';
		$currblk['float_start_y'] = $this->y;
		if ($setwidth) {
			$currblk['margin_right'] = $container_w - ($setwidth + $bdl + $pdl + $bdr + $pdr + $currblk['margin_left']);
			$currblk['float_width'] = ($setwidth + $bdl + $pdl + $bdr + $pdr + $currblk['margin_left']);
		}
		else {
			// *** If no width set - would need to buffer and keep track of max width, then Right-align if not full width
			// and do borders and backgrounds - For now - just set to maximum width left

			if ($r_exists) { $currblk['margin_right'] += $r_width; }
			$currblk['css_set_width'] = $container_w - ($currblk['margin_left'] + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr);

			$currblk['float_width'] = ($currblk['css_set_width'] + $bdl + $pdl + $bdr + $pdr + $currblk['margin_left']);
		}
	}

	else {
		// Don't allow overlap - if floats present - adjust padding to avoid overlap with Floats
		list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl-1);
		$maxw = $container_w - $l_width - $r_width;
		if (($setwidth + $currblk['margin_left'] + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr) > $maxw || ($maxw - ($currblk['margin_right'] + $currblk['margin_left'] + $bdl + $pdl + $bdr + $pdr)) < (2*$this->GetCharWidth('W',false))) {
			// Too narrow to fit - try to move down past L or R float
			if ($l_max < $r_max && ($setwidth + $currblk['margin_left'] + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr) <= ($container_w - $r_width) && (($container_w - $r_width) - ($currblk['margin_right'] + $currblk['margin_left'] + $bdl + $pdl + $bdr + $pdr)) > (2*$this->GetCharWidth('W',false))) {
				$this->ClearFloats('LEFT', $this->blklvl-1); 
			}
			else if ($r_max < $l_max && ($setwidth + $currblk['margin_left'] + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr) <= ($container_w - $l_width) && (($container_w - $l_width) - ($currblk['margin_right'] + $currblk['margin_left'] + $bdl + $pdl + $bdr + $pdr)) > (2*$this->GetCharWidth('W',false))) {
				$this->ClearFloats('RIGHT', $this->blklvl-1); 
			}
			else { $this->ClearFloats('BOTH', $this->blklvl-1); }
			list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl-1);
		}
		if ($r_exists) { $currblk['padding_right'] = max(($r_width-$currblk['margin_right']-$bdr), $pdr); }
		if ($l_exists) { $currblk['padding_left'] = max(($l_width-$currblk['margin_left']-$bdl), $pdl); }
	}
/*-- END CSS-FLOAT --*/


/*-- BORDER-RADIUS --*/
	// Automatically increase padding if required for border-radius
	if ($this->autoPadding && !$this->ColActive && !$this->keep_block_together) {
	  if ($currblk['border_radius_TL_H']>$currblk['padding_left'] && $currblk['border_radius_TL_V']>$currblk['padding_top']) {
		if ($currblk['border_radius_TL_H']>$currblk['border_radius_TL_V']) {
			$this->_borderPadding($currblk['border_radius_TL_H'],$currblk['border_radius_TL_V'], $currblk['padding_left'], $currblk['padding_top']);
		}
		else {
			$this->_borderPadding($currblk['border_radius_TL_V'],$currblk['border_radius_TL_H'], $currblk['padding_top'], $currblk['padding_left']);
		}
	  }
	  if ($currblk['border_radius_TR_H']>$currblk['padding_right'] && $currblk['border_radius_TR_V']>$currblk['padding_top']) {
		if ($currblk['border_radius_TR_H']>$currblk['border_radius_TR_V']) {
			$this->_borderPadding($currblk['border_radius_TR_H'],$currblk['border_radius_TR_V'], $currblk['padding_right'], $currblk['padding_top']);
		}
		else {
			$this->_borderPadding($currblk['border_radius_TR_V'],$currblk['border_radius_TR_H'], $currblk['padding_top'], $currblk['padding_right']);
		}
	  }
	  if ($currblk['border_radius_BL_H']>$currblk['padding_left'] && $currblk['border_radius_BL_V']>$currblk['padding_bottom']) {
		if ($currblk['border_radius_BL_H']>$currblk['border_radius_BL_V']) {
			$this->_borderPadding($currblk['border_radius_BL_H'],$currblk['border_radius_BL_V'], $currblk['padding_left'], $currblk['padding_bottom']);
		}
		else {
			$this->_borderPadding($currblk['border_radius_BL_V'],$currblk['border_radius_BL_H'], $currblk['padding_bottom'], $currblk['padding_left']);
		}
	  }
	  if ($currblk['border_radius_BR_H']>$currblk['padding_right'] && $currblk['border_radius_BR_V']>$currblk['padding_bottom']) {
		if ($currblk['border_radius_BR_H']>$currblk['border_radius_BR_V']) {
			$this->_borderPadding($currblk['border_radius_BR_H'],$currblk['border_radius_BR_V'], $currblk['padding_right'], $currblk['padding_bottom']);
		}
		else {
			$this->_borderPadding($currblk['border_radius_BR_V'],$currblk['border_radius_BR_H'], $currblk['padding_bottom'], $currblk['padding_right']);
		}
	  }
	}
/*-- END BORDER-RADIUS --*/


	// Hanging indent - if negative indent: ensure padding is >= indent
	if(!isset($currblk['text_indent'])) { $currblk['text_indent'] = null; }
	if(!isset($currblk['inner_width'])) { $currblk['inner_width'] = null; }
	$cbti = $this->ConvertSize($currblk['text_indent'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); 
	if ($cbti < 0) {
	  $hangind = -($cbti);
	  if ($currblk['direction'] == 'rtl') {	// *RTL*
		$currblk['padding_right'] = max($currblk['padding_right'],$hangind);	// *RTL*
	  }	// *RTL*
	  else {	// *RTL*
		$currblk['padding_left'] = max($currblk['padding_left'],$hangind);
	  }	// *RTL*
	}

	if (isset($currblk['css_set_width'])) {
	  if (isset($properties['MARGIN-LEFT']) && isset($properties['MARGIN-RIGHT']) && strtolower($properties['MARGIN-LEFT'])=='auto' && strtolower($properties['MARGIN-RIGHT'])=='auto') { 
		  // Try to reduce margins to accomodate - if still too wide, set margin-right/left=0 (reduces width)
		  $anyextra = $prevblk['inner_width'] - ($currblk['css_set_width'] + $currblk['border_left']['w'] + $currblk['padding_left'] + $currblk['border_right']['w'] + $currblk['padding_right']);
		  if ($anyextra>0) {
			$currblk['margin_left'] = $currblk['margin_right'] = $anyextra /2;
		  }
		  else {
			$currblk['margin_left'] = $currblk['margin_right'] = 0;
		  }
	  }
	  else if (isset($properties['MARGIN-LEFT']) && strtolower($properties['MARGIN-LEFT'])=='auto') { 
		  // Try to reduce margin-left to accomodate - if still too wide, set margin-left=0 (reduces width)
		  $currblk['margin_left'] = $prevblk['inner_width'] - ($currblk['css_set_width'] + $currblk['border_left']['w'] + $currblk['padding_left'] + $currblk['border_right']['w'] + $currblk['padding_right'] + $currblk['margin_right']);
		  if ($currblk['margin_left'] < 0) {
			$currblk['margin_left'] = 0;
		  }
	  }
	  else if (isset($properties['MARGIN-RIGHT']) && strtolower($properties['MARGIN-RIGHT'])=='auto') { 
		  // Try to reduce margin-right to accomodate - if still too wide, set margin-right=0 (reduces width)
		  $currblk['margin_right'] = $prevblk['inner_width'] - ($currblk['css_set_width'] + $currblk['border_left']['w'] + $currblk['padding_left'] + $currblk['border_right']['w'] + $currblk['padding_right'] + $currblk['margin_left']);
		  if ($currblk['margin_right'] < 0) {
			$currblk['margin_right'] = 0;
		  }
	  }
	  else { 
	    if ($currblk['direction'] == 'rtl') {	// *RTL*
		// Try to reduce margin-left to accomodate - if still too wide, set margin-left=0 (reduces width)
		$currblk['margin_left'] = $prevblk['inner_width'] - ($currblk['css_set_width'] + $currblk['border_left']['w'] + $currblk['padding_left'] + $currblk['border_right']['w'] + $currblk['padding_right'] + $currblk['margin_right']);	// *RTL*
		if ($currblk['margin_left'] < 0) {	// *RTL*
			$currblk['margin_left'] = 0;	// *RTL*
		}	// *RTL*
	    }	// *RTL*
	    else {	// *RTL*
		  // Try to reduce margin-right to accomodate - if still too wide, set margin-right=0 (reduces width)
		  $currblk['margin_right'] = $prevblk['inner_width'] - ($currblk['css_set_width'] + $currblk['border_left']['w'] + $currblk['padding_left'] + $currblk['border_right']['w'] + $currblk['padding_right'] + $currblk['margin_left']);
		  if ($currblk['margin_right'] < 0) {
			$currblk['margin_right'] = 0;
		  }
	    }	// *RTL*
	  }
	}

	$currblk['outer_left_margin'] = $prevblk['outer_left_margin'] + $currblk['margin_left'] + $prevblk['border_left']['w'] + $prevblk['padding_left'];
	$currblk['outer_right_margin'] = $prevblk['outer_right_margin']  + $currblk['margin_right'] + $prevblk['border_right']['w'] + $prevblk['padding_right'];

	$currblk['width'] = $this->pgwidth - ($currblk['outer_right_margin'] + $currblk['outer_left_margin']);
	$currblk['inner_width'] = $currblk['width'] - ($currblk['border_left']['w'] + $currblk['padding_left'] + $currblk['border_right']['w'] + $currblk['padding_right']);

	// Check DIV is not now too narrow to fit text
	$mw = 2*$this->GetCharWidth('W',false);
	if ($currblk['inner_width'] < $mw) {
		$currblk['padding_left'] = 0;
		$currblk['padding_right'] = 0;
		$currblk['border_left']['w'] = 0.2;
		$currblk['border_right']['w'] = 0.2;
		$currblk['margin_left'] = 0;
		$currblk['margin_right'] = 0;
		$currblk['outer_left_margin'] = $prevblk['outer_left_margin'] + $currblk['margin_left'] + $prevblk['border_left']['w'] + $prevblk['padding_left'];
		$currblk['outer_right_margin'] = $prevblk['outer_right_margin']  + $currblk['margin_right'] + $prevblk['border_right']['w'] + $prevblk['padding_right'];
		$currblk['width'] = $this->pgwidth - ($currblk['outer_right_margin'] + $currblk['outer_left_margin']);
		$currblk['inner_width'] = $this->pgwidth - ($currblk['outer_right_margin'] + $currblk['outer_left_margin'] + $currblk['border_left']['w'] + $currblk['padding_left'] + $currblk['border_right']['w'] + $currblk['padding_right']);
//		if ($currblk['inner_width'] < $mw) { $this->Error("DIV is too narrow for text to fit!"); }
	}

	$this->x = $this->lMargin + $currblk['outer_left_margin'];

/*-- BACKGROUNDS --*/
	if (isset($properties['BACKGROUND-IMAGE']) && $properties['BACKGROUND-IMAGE'] && !$this->kwt && !$this->ColActive && !$this->keep_block_together) {
		$ret = $this->SetBackground($properties, $currblk['inner_width']);
		if ($ret) { $currblk['background-image'] = $ret; }
	}
/*-- END BACKGROUNDS --*/

/*-- TABLES --*/
	if ($this->use_kwt && isset($attr['KEEP-WITH-TABLE']) && !$this->ColActive && !$this->keep_block_together) {
		$this->kwt = true;
		$this->kwt_y0 = $this->y;
		$this->kwt_x0 = $this->x;
		$this->kwt_height = 0;
		$this->kwt_buffer = array();
		$this->kwt_Links = array();
		$this->kwt_Annots = array();
		$this->kwt_moved = false;
		$this->kwt_saved = false;
		$this->kwt_Reference = array();
		$this->kwt_BMoutlines = array();
		$this->kwt_toc = array();
	}
	else { 
/*-- END TABLES --*/
		$this->kwt = false; 
	}	// *TABLES*

	//Save x,y coords in case we need to print borders...
	$currblk['y0'] = $this->y;
	$currblk['x0'] = $this->x;
	$currblk['startpage'] = $this->page;
	$this->oldy = $this->y;

	$this->lastblocklevelchange = 1 ;

	break;

    case 'HR':
	// Added mPDF 3.0 Float DIV - CLEAR
	if (isset($attr['STYLE'])) {
		$properties = $this->cssmgr->readInlineCSS($attr['STYLE']);
		if (isset($properties['CLEAR'])) { $this->ClearFloats(strtoupper($properties['CLEAR']),$this->blklvl); }	// *CSS-FLOAT*
	}

	$this->ignorefollowingspaces = true; 

	$objattr = array();
		$objattr['margin_top'] = 0;
		$objattr['margin_bottom'] = 0;
		$objattr['margin_left'] = 0;
		$objattr['margin_right'] = 0;
		$objattr['width'] = 0;
		$objattr['height'] = 0;
		$objattr['border_top']['w'] = 0;
		$objattr['border_bottom']['w'] = 0;
		$objattr['border_left']['w'] = 0;
		$objattr['border_right']['w'] = 0;
	$properties = $this->cssmgr->MergeCSS('',$tag,$attr);
	if (isset($properties['MARGIN-TOP'])) { $objattr['margin_top'] = $this->ConvertSize($properties['MARGIN-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
	if (isset($properties['MARGIN-BOTTOM'])) { $objattr['margin_bottom'] = $this->ConvertSize($properties['MARGIN-BOTTOM'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
	if (isset($properties['WIDTH'])) { $objattr['width'] = $this->ConvertSize($properties['WIDTH'],$this->blk[$this->blklvl]['inner_width']); }
	else if(isset($attr['WIDTH']) && $attr['WIDTH'] != '') $objattr['width'] = $this->ConvertSize($attr['WIDTH'],$this->blk[$this->blklvl]['inner_width']);
	if (isset($properties['TEXT-ALIGN'])) { $objattr['align'] = $align[strtolower($properties['TEXT-ALIGN'])]; }
	else if(isset($attr['ALIGN']) && $attr['ALIGN'] != '') $objattr['align'] = $align[strtolower($attr['ALIGN'])];

	if (isset($properties['MARGIN-LEFT']) && strtolower($properties['MARGIN-LEFT'])=='auto') { 
		$objattr['align'] = 'R';
	}
	if (isset($properties['MARGIN-RIGHT']) && strtolower($properties['MARGIN-RIGHT'])=='auto') { 
		$objattr['align'] = 'L';
		if (isset($properties['MARGIN-RIGHT']) && strtolower($properties['MARGIN-RIGHT'])=='auto' && isset($properties['MARGIN-LEFT']) && strtolower($properties['MARGIN-LEFT'])=='auto') { 
			$objattr['align'] = 'C';
		}
	}
	if (isset($properties['COLOR'])) { $objattr['color'] = $this->ConvertColor($properties['COLOR']); }
	else if(isset($attr['COLOR']) && $attr['COLOR'] != '') $objattr['color'] = $this->ConvertColor($attr['COLOR']);
	if (isset($properties['HEIGHT'])) { $objattr['linewidth'] = $this->ConvertSize($properties['HEIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }


/*-- TABLES --*/
	if ($this->tableLevel) {
		$objattr['W-PERCENT'] = 100;
		if (isset($properties['WIDTH']) && stristr($properties['WIDTH'],'%')) { 
			$properties['WIDTH'] += 0;  //make "90%" become simply "90" 
			$objattr['W-PERCENT'] = $properties['WIDTH'];
		}
		if (isset($attr['WIDTH']) && stristr($attr['WIDTH'],'%')) { 
			$attr['WIDTH'] += 0;  //make "90%" become simply "90" 
			$objattr['W-PERCENT'] = $attr['WIDTH'];
		}
	}
/*-- END TABLES --*/

	$objattr['type'] = 'hr';
	$objattr['height'] = $objattr['linewidth'] + $objattr['margin_top'] + $objattr['margin_bottom'];
	$e = "\xbb\xa4\xactype=image,objattr=".serialize($objattr)."\xbb\xa4\xac";

	// Clear properties - tidy up
	$properties = array();

/*-- TABLES --*/
	// Output it to buffers
	if ($this->tableLevel) {
		if (!isset($this->cell[$this->row][$this->col]['maxs'])) {
			$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s'];
		}
		elseif($this->cell[$this->row][$this->col]['maxs'] < $this->cell[$this->row][$this->col]['s']) {
			$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s']; 
		}
		$this->cell[$this->row][$this->col]['s'] = 0 ;// reset
		$this->_saveCellTextBuffer($e, $this->HREF);
	}
	else {
/*-- END TABLES --*/
		$this->_saveTextBuffer($e, $this->HREF);
	}	// *TABLES*

	break;


/*-- BARCODES --*/
    
    case 'BARCODE':
	if(isset($attr['CODE']) && $attr['CODE']) {
		$objattr = array();
		$objattr['margin_top'] = 0;
		$objattr['margin_bottom'] = 0;
		$objattr['margin_left'] = 0;
		$objattr['margin_right'] = 0;
		$objattr['padding_top'] = 0;
		$objattr['padding_bottom'] = 0;
		$objattr['padding_left'] = 0;
		$objattr['padding_right'] = 0;
		$objattr['width'] = 0;
		$objattr['height'] = 0;
		$objattr['border_top']['w'] = 0;
		$objattr['border_bottom']['w'] = 0;
		$objattr['border_left']['w'] = 0;
		$objattr['border_right']['w'] = 0;
     		$objattr['code'] = $attr['CODE'];

		if(isset($attr['TYPE'])) {
			$objattr['btype'] = trim(strtoupper($attr['TYPE']));
		}
		else { $objattr['btype'] = 'EAN13'; }	// default
		if (preg_match('/^(EAN13|ISBN|ISSN|EAN8|UPCA|UPCE)P([25])$/',$objattr['btype'],$m)) {
			$objattr['btype'] = $m[1];
			$objattr['bsupp'] = $m[2];
			if (preg_match('/^(\S+)\s+(.*)$/',$objattr['code'],$mm)) {
				$objattr['code'] = $mm[1];
				$objattr['bsupp_code'] = $mm[2];
			}
		}
		else { $objattr['bsupp'] = 0; }

		if(isset($attr['TEXT']) && $attr['TEXT']==1) { $objattr['showtext'] = 1; }
		else { $objattr['showtext'] = 0; }
		if(isset($attr['SIZE']) && $attr['SIZE']>0) { $objattr['bsize'] = $attr['SIZE']; }
		else { $objattr['bsize'] = 1; }
		if(isset($attr['HEIGHT']) && $attr['HEIGHT']>0) { $objattr['bheight'] = $attr['HEIGHT']; }
		else { $objattr['bheight'] = 1; }
		if(isset($attr['PR']) && $attr['PR']>0) { $objattr['pr_ratio'] = $attr['PR']; }
		else { $objattr['pr_ratio'] = ''; }
		$properties = $this->cssmgr->MergeCSS('',$tag,$attr);
		if(isset($properties ['DISPLAY']) && strtolower($properties ['DISPLAY'])=='none') { 
			return; 
		}
		if (isset($properties['MARGIN-TOP'])) { $objattr['margin_top']=$this->ConvertSize($properties['MARGIN-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if (isset($properties['MARGIN-BOTTOM'])) { $objattr['margin_bottom'] = $this->ConvertSize($properties['MARGIN-BOTTOM'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if (isset($properties['MARGIN-LEFT'])) { $objattr['margin_left'] = $this->ConvertSize($properties['MARGIN-LEFT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if (isset($properties['MARGIN-RIGHT'])) { $objattr['margin_right'] = $this->ConvertSize($properties['MARGIN-RIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }

		if (isset($properties['PADDING-TOP'])) { $objattr['padding_top']=$this->ConvertSize($properties['PADDING-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if (isset($properties['PADDING-BOTTOM'])) { $objattr['padding_bottom'] = $this->ConvertSize($properties['PADDING-BOTTOM'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if (isset($properties['PADDING-LEFT'])) { $objattr['padding_left'] = $this->ConvertSize($properties['PADDING-LEFT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if (isset($properties['PADDING-RIGHT'])) { $objattr['padding_right'] = $this->ConvertSize($properties['PADDING-RIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }

		if (isset($properties['BORDER-TOP'])) { $objattr['border_top'] = $this->border_details($properties['BORDER-TOP']); }
		if (isset($properties['BORDER-BOTTOM'])) { $objattr['border_bottom'] = $this->border_details($properties['BORDER-BOTTOM']); }
		if (isset($properties['BORDER-LEFT'])) { $objattr['border_left'] = $this->border_details($properties['BORDER-LEFT']); }
		if (isset($properties['BORDER-RIGHT'])) { $objattr['border_right'] = $this->border_details($properties['BORDER-RIGHT']); }

		if (isset($properties['VERTICAL-ALIGN'])) { $objattr['vertical-align'] = $align[strtolower($properties['VERTICAL-ALIGN'])]; }
		if (isset($properties['COLOR']) && $properties['COLOR'] != '') { $objattr['color'] = $this->ConvertColor($properties['COLOR']); }
		else { $objattr['color'] = false; }
		if (isset($properties['BACKGROUND-COLOR']) && $properties['BACKGROUND-COLOR'] != '') { $objattr['bgcolor'] = $this->ConvertColor($properties['BACKGROUND-COLOR']); }
		else { $objattr['bgcolor'] = false; }

		if (!class_exists('PDFBarcode', false)) { 
			include(_MPDF_PATH.'classes/barcode.php'); 
		}
		$this->barcode = new PDFBarcode();

		if ($objattr['btype'] == 'EAN13' || $objattr['btype'] == 'ISBN' || $objattr['btype'] == 'ISSN' || $objattr['btype'] == 'UPCA' || $objattr['btype'] == 'UPCE' || $objattr['btype'] == 'EAN8') {
			$code = preg_replace('/\-/','',$objattr['code']);
			if ($objattr['btype'] == 'ISSN' || $objattr['btype'] == 'ISBN') {
				$arrcode = $this->barcode->getBarcodeArray($code, 'EAN13');
			}
			else { $arrcode = $this->barcode->getBarcodeArray($code, $objattr['btype'] ); }
			if ($arrcode === false) { $this->Error('Error in barcode string.'); }

			if ($objattr['bsupp'] == 2 || $objattr['bsupp'] == 5) {	// EAN-2 or -5 Supplement
				$supparrcode = $this->barcode->getBarcodeArray($objattr['bsupp_code'], 'EAN'.$objattr['bsupp'] ); 
				$w = ($arrcode["maxw"] + $arrcode['lightmL'] + $arrcode['lightmR'] + $supparrcode["maxw"] + $supparrcode['sepM']) * $arrcode['nom-X'] * $objattr['bsize'];
			}
			else {
				$w = ($arrcode["maxw"] + $arrcode['lightmL'] + $arrcode['lightmR']) * $arrcode['nom-X'] * $objattr['bsize'];
			}
			$h = $arrcode['nom-H'] * $objattr['bsize'] * $objattr['bheight'];
			// Add height for ISBN string + margin from top of bars
			if (($objattr['showtext'] && $objattr['btype'] == 'EAN13') || $objattr['btype'] == 'ISBN' || $objattr['btype'] == 'ISSN') { 
				$tisbnm = 1.5 * $objattr['bsize'];	// Top margin between TOP TEXT (isbn - if shown) & bars
				$isbn_fontsize = 2.1 * $objattr['bsize'];
				$h += $isbn_fontsize + $tisbnm  ; 
			}
		}
		// QR-code
		else if ($objattr['btype'] == 'QR') {
			$w = $h = $objattr['bsize']*25;	// Factor of 25mm (default)
			$objattr['errorlevel'] = 'L';
			if (isset($attr['ERROR'])) { $objattr['errorlevel'] = $attr['ERROR']; }
		}
		else if ($objattr['btype'] == 'IMB' || $objattr['btype'] == 'RM4SCC' || $objattr['btype'] == 'KIX' || $objattr['btype'] == 'POSTNET' || $objattr['btype'] == 'PLANET') {
			$arrcode = $this->barcode->getBarcodeArray($objattr['code'], $objattr['btype'] ); 
			if ($arrcode === false) { $this->Error('Error in barcode string.'); }
			$w = ($arrcode["maxw"] * $arrcode['nom-X'] * $objattr['bsize']) + $arrcode['quietL'] + $arrcode['quietR'];
			$h = ($arrcode['nom-H'] * $objattr['bsize']) + (2*$arrcode['quietTB']);
		}
		else if (in_array($objattr['btype'], array('C128A','C128B','C128C','EAN128A','EAN128B','EAN128C','C39','C39+','C39E','C39E+','S25','S25+','I25','I25+','I25B','I25B+','C93','MSI','MSI+','CODABAR','CODE11'))) {
			$arrcode = $this->barcode->getBarcodeArray($objattr['code'], $objattr['btype'], $objattr['pr_ratio'] );
			if ($arrcode === false) { $this->Error('Error in barcode string.'); }
			$w = ($arrcode["maxw"] + $arrcode['lightmL'] + $arrcode['lightmR']) * $arrcode['nom-X'] * $objattr['bsize'];
			$h = ((2*$arrcode['lightTB'] * $arrcode['nom-X']) + $arrcode['nom-H']) * $objattr['bsize'] * $objattr['bheight'];
		}
		else { break; }

		$extraheight = $objattr['padding_top'] + $objattr['padding_bottom'] + $objattr['margin_top'] + $objattr['margin_bottom'] + $objattr['border_top']['w'] + $objattr['border_bottom']['w'];
		$extrawidth = $objattr['padding_left'] + $objattr['padding_right'] + $objattr['margin_left'] + $objattr['margin_right'] + $objattr['border_left']['w'] + $objattr['border_right']['w'];

		$objattr['type'] = 'barcode';
		$objattr['height'] = $h + $extraheight;
		$objattr['width'] = $w + $extrawidth;
		$objattr['barcode_height'] = $h;
		$objattr['barcode_width'] = $w;
/*-- CSS-IMAGE-FLOAT --*/
		if (!$this->ColActive && !$this->tableLevel && !$this->listlvl && !$this->kwt && !$this->keep_block_together) {
		  if (isset($properties['FLOAT']) && (strtoupper($properties['FLOAT']) == 'RIGHT' || strtoupper($properties['FLOAT']) == 'LEFT')) {
			$objattr['float'] = substr(strtoupper($properties['FLOAT']),0,1);
		  }
		}
/*-- END CSS-IMAGE-FLOAT --*/

		$e = "\xbb\xa4\xactype=barcode,objattr=".serialize($objattr)."\xbb\xa4\xac";

		// Clear properties - tidy up
		$properties = array();

/*-- TABLES --*/
		// Output it to buffers
		if ($this->tableLevel) {
			$this->_saveCellTextBuffer($e, $this->HREF);
			$this->cell[$this->row][$this->col]['s'] += $objattr['width'] ;
		}
		else {
/*-- END TABLES --*/
			$this->_saveTextBuffer($e, $this->HREF);

		}	// *TABLES*
	}
	break;
/*-- END BARCODES --*/


	// *********** FORM ELEMENTS ********************

/*-- FORMS --*/
    case 'SELECT':
	$this->lastoptionaltag = ''; // Save current HTML specified optional endtag
	$this->InlineProperties[$tag] = $this->saveInlineProperties();
	$properties = $this->cssmgr->MergeCSS('',$tag,$attr);
	if (isset($properties['FONT-FAMILY'])) { 
		$this->SetFont($properties['FONT-FAMILY'],$this->FontStyle,0,false);
	}
	if (isset($properties['FONT-SIZE'])) { 
		$mmsize = $this->ConvertSize($properties['FONT-SIZE'],$this->default_font_size/_MPDFK);
  		$this->SetFontSize($mmsize*_MPDFK,false);
	}
	if (isset($attr['SPELLCHECK']) && strtolower($attr['SPELLCHECK'])=='true') {
		$this->selectoption['SPELLCHECK'] = true; 
	}

	if (isset($properties['COLOR'])) { $this->selectoption['COLOR'] = $this->ConvertColor($properties['COLOR']); }
	$this->specialcontent = "type=select"; 
	if(isset($attr['DISABLED'])) { $this->selectoption['DISABLED'] = $attr['DISABLED']; }
	if(isset($attr['READONLY'])) { $this->selectoption['READONLY'] = $attr['READONLY']; }
	if(isset($attr['REQUIRED'])) { $this->selectoption['REQUIRED'] = $attr['REQUIRED']; }
	if(isset($attr['EDITABLE'])) { $this->selectoption['EDITABLE'] = $attr['EDITABLE']; }
	if(isset($attr['TITLE'])) { $this->selectoption['TITLE'] = $attr['TITLE']; }
	if(isset($attr['MULTIPLE'])) { $this->selectoption['MULTIPLE'] = $attr['MULTIPLE']; }
	if(isset($attr['SIZE']) && $attr['SIZE']>1) { $this->selectoption['SIZE'] = $attr['SIZE']; }
	if ($this->useActiveForms) {
		if(isset($attr['NAME'])) { $this->selectoption['NAME'] = $attr['NAME']; }
		if (isset($attr['ONCHANGE'])) { $this->selectoption['ONCHANGE'] = $attr['ONCHANGE']; }
	}

	$properties = array();
	break;

    case 'OPTION':
	$this->lastoptionaltag = 'OPTION'; // Save current HTML specified optional endtag
	$this->selectoption['ACTIVE'] = true;
	$this->selectoption['currentSEL'] = false;
	if (empty($this->selectoption)) {
		$this->selectoption['MAXWIDTH'] = '';
		$this->selectoption['SELECTED'] = '';
	}
	if (isset($attr['SELECTED'])) { 
		$this->selectoption['SELECTED'] = '';
		$this->selectoption['currentSEL'] = true;
	}
	if(isset($attr['VALUE'])) { 
		$attr['VALUE'] = strcode2utf($attr['VALUE']);
		$attr['VALUE'] = $this->lesser_entity_decode($attr['VALUE']);
		if ($this->onlyCoreFonts)
			$attr['VALUE'] = mb_convert_encoding($attr['VALUE'], $this->mb_enc,'UTF-8'); 
	}
	$this->selectoption['currentVAL'] = $attr['VALUE'];
	break;

    case 'TEXTAREA':
	$objattr = array();
		$objattr['margin_top'] = 0;
		$objattr['margin_bottom'] = 0;
		$objattr['margin_left'] = 0;
		$objattr['margin_right'] = 0;
		$objattr['width'] = 0;
		$objattr['height'] = 0;
		$objattr['border_top']['w'] = 0;
		$objattr['border_bottom']['w'] = 0;
		$objattr['border_left']['w'] = 0;
		$objattr['border_right']['w'] = 0;
	if(isset($attr['DISABLED'])) { $objattr['disabled'] = true; }
	if(isset($attr['READONLY'])) { $objattr['readonly'] = true; }
	if(isset($attr['REQUIRED'])) { $objattr['required'] = true; }
	if(isset($attr['SPELLCHECK']) && strtolower($attr['SPELLCHECK'])=='true') { $objattr['spellcheck'] = true; }
	if(isset($attr['TITLE'])) { $objattr['title'] = $attr['TITLE']; }
	if ($this->onlyCoreFonts)
		$objattr['title'] = mb_convert_encoding($objattr['title'], $this->mb_enc,'UTF-8'); 
	if ($this->useActiveForms) {
		if(isset($attr['NAME'])) { $objattr['fieldname'] = $attr['NAME']; }
		$this->form->form_element_spacing['textarea']['outer']['v'] = 0;
		$this->form->form_element_spacing['textarea']['inner']['v'] = 0;
		if (isset($attr['ONCALCULATE'])) { $objattr['onCalculate'] = $attr['ONCALCULATE']; }
		else if (isset($attr['ONCHANGE'])) { $objattr['onCalculate'] = $attr['ONCHANGE']; }
		if (isset($attr['ONVALIDATE'])) { $objattr['onValidate'] = $attr['ONVALIDATE']; }
		if (isset($attr['ONKEYSTROKE'])) { $objattr['onKeystroke'] = $attr['ONKEYSTROKE']; }
		if (isset($attr['ONFORMAT'])) { $objattr['onFormat'] = $attr['ONFORMAT']; }
	}
	$this->InlineProperties[$tag] = $this->saveInlineProperties();
	$properties = $this->cssmgr->MergeCSS('',$tag,$attr);
	if (isset($properties['FONT-FAMILY'])) { 
		$this->SetFont($properties['FONT-FAMILY'],'',0,false);
	}
	if (isset($properties['FONT-SIZE'])) { 
		$mmsize = $this->ConvertSize($properties['FONT-SIZE'],$this->default_font_size/_MPDFK);
  		$this->SetFontSize($mmsize*_MPDFK,false);
	}
	if (isset($properties['COLOR'])) { $objattr['color'] = $this->ConvertColor($properties['COLOR']); }
	$objattr['fontfamily'] = $this->FontFamily;
	$objattr['fontsize'] = $this->FontSizePt;
	if ($this->useActiveForms) {
		if(isset($properties['TEXT-ALIGN'])) { $objattr['text_align'] = $align[strtolower($properties['TEXT-ALIGN'])]; }
		else if(isset($attr['ALIGN'])) { $objattr['text_align'] =  $align[strtolower($attr['ALIGN'])]; }
		if (isset($properties['OVERFLOW']) && strtolower($properties['OVERFLOW'])=='hidden') { $objattr['donotscroll'] = true; }
		if (isset($properties['BORDER-TOP-COLOR'])) { $objattr['border-col'] = $this->ConvertColor($properties['BORDER-TOP-COLOR']); }
		if (isset($properties['BACKGROUND-COLOR'])) { $objattr['background-col'] = $this->ConvertColor($properties['BACKGROUND-COLOR']); }
	}
	$this->SetLineHeight('',$this->form->textarea_lineheight); 
	$formLineHeight = $this->lineheight;

	$w = 0;
	$h = 0;
	if(isset($properties['WIDTH'])) $w = $this->ConvertSize($properties['WIDTH'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
	if(isset($properties['HEIGHT'])) $h = $this->ConvertSize($properties['HEIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
	if ($properties['VERTICAL-ALIGN']) { $objattr['vertical-align'] = $align[strtolower($properties['VERTICAL-ALIGN'])]; }

	$colsize = 20; //HTML default value 
	$rowsize = 2; //HTML default value
	if (isset($attr['COLS'])) $colsize = intval($attr['COLS']);
	if (isset($attr['ROWS'])) $rowsize = intval($attr['ROWS']);

	$charsize = $this->GetCharWidth('w',false);
	if ($w) { $colsize = round(($w-($this->form->form_element_spacing['textarea']['outer']['h']*2)-($this->form->form_element_spacing['textarea']['inner']['h']*2))/$charsize); }
	if ($h) { $rowsize = round(($h-($this->form->form_element_spacing['textarea']['outer']['v']*2)-($this->form->form_element_spacing['textarea']['inner']['v']*2))/$formLineHeight); }

	$objattr['type'] = 'textarea';
	$objattr['width'] = ($colsize * $charsize) + ($this->form->form_element_spacing['textarea']['outer']['h']*2)+($this->form->form_element_spacing['textarea']['inner']['h']*2);
	$objattr['height'] = ($rowsize * $formLineHeight) + ($this->form->form_element_spacing['textarea']['outer']['v']*2)+($this->form->form_element_spacing['textarea']['inner']['v']*2);
	$objattr['rows'] = $rowsize;
	$objattr['cols'] = $colsize;

	$this->specialcontent = serialize($objattr); 

	if ($this->tableLevel) {	// *TABLES*
		$this->cell[$this->row][$this->col]['s'] += $objattr['width'] ;	// *TABLES*
	}	// *TABLES*

	// Clear properties - tidy up
	$properties = array();
	break;



	// *********** FORM - INPUT ********************

    case 'INPUT':
	if (!isset($attr['TYPE'])) $attr['TYPE'] == 'TEXT'; 
	$objattr = array();
		$objattr['margin_top'] = 0;
		$objattr['margin_bottom'] = 0;
		$objattr['margin_left'] = 0;
		$objattr['margin_right'] = 0;
		$objattr['width'] = 0;
		$objattr['height'] = 0;
		$objattr['border_top']['w'] = 0;
		$objattr['border_bottom']['w'] = 0;
		$objattr['border_left']['w'] = 0;
		$objattr['border_right']['w'] = 0;
	$objattr['type'] = 'input';
	if(isset($attr['DISABLED'])) { $objattr['disabled'] = true; }
	if(isset($attr['READONLY'])) { $objattr['readonly'] = true; }
	if(isset($attr['REQUIRED'])) { $objattr['required'] = true; }
	if(isset($attr['SPELLCHECK']) && strtolower($attr['SPELLCHECK'])=='true') { $objattr['spellcheck'] = true; }
	if(isset($attr['TITLE'])) { $objattr['title'] = $attr['TITLE']; }
	else if(isset($attr['ALT'])) { $objattr['title'] = $attr['ALT']; }
	else $objattr['title'] = '';
	$objattr['title'] = strcode2utf($objattr['title']);
	$objattr['title'] = $this->lesser_entity_decode($objattr['title']);
	if ($this->onlyCoreFonts)
		$objattr['title'] = mb_convert_encoding($objattr['title'], $this->mb_enc,'UTF-8'); 
	if ($this->useActiveForms) {
		if(isset($attr['NAME'])) { $objattr['fieldname'] = $attr['NAME']; }
	}
	if(isset($attr['VALUE'])) { 
		$attr['VALUE'] = strcode2utf($attr['VALUE']);
		$attr['VALUE'] = $this->lesser_entity_decode($attr['VALUE']);
		if ($this->onlyCoreFonts)
			$attr['VALUE'] = mb_convert_encoding($attr['VALUE'], $this->mb_enc,'UTF-8'); 
		$objattr['value'] = $attr['VALUE']; 
	}

	$this->InlineProperties[$tag] = $this->saveInlineProperties();
	$properties = $this->cssmgr->MergeCSS('',$tag,$attr);
	$objattr['vertical-align'] = '';

	if (isset($properties['FONT-FAMILY'])) { 
		$this->SetFont($properties['FONT-FAMILY'],$this->FontStyle,0,false);
	}
	if (isset($properties['FONT-SIZE'])) { 
		$mmsize = $this->ConvertSize($properties['FONT-SIZE'],($this->default_font_size/_MPDFK));
  		$this->SetFontSize($mmsize*_MPDFK,false);
	}
	if (isset($properties['COLOR'])) { $objattr['color'] = $this->ConvertColor($properties['COLOR']); }
	$objattr['fontfamily'] = $this->FontFamily;
	$objattr['fontsize'] = $this->FontSizePt;
	if ($this->useActiveForms) {
		if(isset($attr['ALIGN'])) { $objattr['text_align'] = $align[strtolower($attr['ALIGN'])]; }
		else if(isset($properties['TEXT-ALIGN'])) { $objattr['text_align'] = $align[strtolower($properties['TEXT-ALIGN'])]; }
		if (isset($properties['BORDER-TOP-COLOR'])) { $objattr['border-col'] = $this->ConvertColor($properties['BORDER-TOP-COLOR']); }
		if (isset($properties['BACKGROUND-COLOR'])) { $objattr['background-col'] = $this->ConvertColor($properties['BACKGROUND-COLOR']); }
	}
 
	$type = '';
      $texto='';
	$height = $this->FontSize;
	$width = 0;
	$spacesize = $this->GetCharWidth(' ',false);

	$w = 0;
	if(isset($properties['WIDTH'])) $w = $this->ConvertSize($properties['WIDTH'],$this->blk[$this->blklvl]['inner_width']);

	if ($properties['VERTICAL-ALIGN']) { $objattr['vertical-align'] = $align[strtolower($properties['VERTICAL-ALIGN'])]; }

	switch(strtoupper($attr['TYPE'])){
	   case 'HIDDEN':
      		$this->ignorefollowingspaces = true; //Eliminate exceeding left-side spaces
			if ($this->useActiveForms) {
				$this->form->SetFormText( 0, 0, $objattr['fieldname'], $objattr['value'], $objattr['value'], '', 0, '', true );
			}
			if ($this->InlineProperties[$tag]) { $this->restoreInlineProperties($this->InlineProperties[$tag]); }
			unset($this->InlineProperties[$tag]);
			break 2;
	   case 'CHECKBOX': //Draw Checkbox
			$type = 'CHECKBOX';
			if (isset($attr['CHECKED'])) { $objattr['checked'] = true; } 
			else { $objattr['checked'] = false; }
			$width = $this->FontSize;
			$height = $this->FontSize;
			break;

	   case 'RADIO': //Draw Radio button
			$type = 'RADIO';
			if (isset($attr['CHECKED'])) $objattr['checked'] = true;
			$width = $this->FontSize;
			$height = $this->FontSize;
			break;

/*-- IMAGES-CORE --*/
	   case 'IMAGE': // Draw an Image button
	if(isset($attr['SRC']))	{
		$type = 'IMAGE';
     		$srcpath = $attr['SRC'];
		$orig_srcpath = $attr['ORIG_SRC'];
		// VSPACE and HSPACE converted to margins in MergeCSS
		if (isset($properties['MARGIN-TOP'])) { $objattr['margin_top']=$this->ConvertSize($properties['MARGIN-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if (isset($properties['MARGIN-BOTTOM'])) { $objattr['margin_bottom'] = $this->ConvertSize($properties['MARGIN-BOTTOM'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if (isset($properties['MARGIN-LEFT'])) { $objattr['margin_left'] = $this->ConvertSize($properties['MARGIN-LEFT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if (isset($properties['MARGIN-RIGHT'])) { $objattr['margin_right'] = $this->ConvertSize($properties['MARGIN-RIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }


		if (isset($properties['BORDER-TOP'])) { $objattr['border_top'] = $this->border_details($properties['BORDER-TOP']); }
		if (isset($properties['BORDER-BOTTOM'])) { $objattr['border_bottom'] = $this->border_details($properties['BORDER-BOTTOM']); }
		if (isset($properties['BORDER-LEFT'])) { $objattr['border_left'] = $this->border_details($properties['BORDER-LEFT']); }
		if (isset($properties['BORDER-RIGHT'])) { $objattr['border_right'] = $this->border_details($properties['BORDER-RIGHT']); }

		$objattr['padding_top'] = 0;
		$objattr['padding_bottom'] = 0;
		$objattr['padding_left'] = 0;
		$objattr['padding_right'] = 0;

		if (isset($properties['VERTICAL-ALIGN'])) { $objattr['vertical-align'] = $align[strtolower($properties['VERTICAL-ALIGN'])]; }

		$w = 0;
		$h = 0;
		if(isset($properties['WIDTH'])) $w = $this->ConvertSize($properties['WIDTH'],$this->blk[$this->blklvl]['inner_width']);
		if(isset($properties['HEIGHT'])) $h = $this->ConvertSize($properties['HEIGHT'],$this->blk[$this->blklvl]['inner_width']);

		$extraheight = $objattr['margin_top'] + $objattr['margin_bottom'] + $objattr['border_top']['w'] + $objattr['border_bottom']['w'];
		$extrawidth = $objattr['margin_left'] + $objattr['margin_right'] + $objattr['border_left']['w'] + $objattr['border_right']['w'];

		// Image file
		$info=$this->_getImage($srcpath, true, true, $orig_srcpath);
		if(!$info) {
			$info = $this->_getImage($this->noImageFile);
			if ($info) { 
				$srcpath = $this->noImageFile; 
				$w = ($info['w'] * (25.4/$this->dpi)); 
				$h = ($info['h'] * (25.4/$this->dpi));
			}
		}
		if(!$info) break;
		if ($info['cs']=='Indexed') { $objattr['Indexed'] = true; }
		$objattr['file'] = $srcpath;
		//Default width and height calculation if needed
		if($w==0 and $h==0) {
/*-- IMAGES-WMF --*/
      	      if ($info['type']=='wmf') { 
				// WMF units are twips (1/20pt)
				// divide by 20 to get points
				// divide by k to get user units
				$w = abs($info['w'])/(20*_MPDFK);
				$h = abs($info['h']) / (20*_MPDFK);
			}
			else 
/*-- END IMAGES-WMF --*/
      	      if ($info['type']=='svg') { 
				// SVG units are pixels
				$w = abs($info['w'])/_MPDFK;
				$h = abs($info['h'])/_MPDFK;
			}
			else {
				//Put image at default image dpi
				$w=($info['w']/_MPDFK) * (72/$this->img_dpi);
				$h=($info['h']/_MPDFK) * (72/$this->img_dpi);
			}
			if (isset($properties['IMAGE-RESOLUTION'])) { 
				if (preg_match('/from-image/i', $properties['IMAGE-RESOLUTION']) && isset($info['set-dpi']) && $info['set-dpi']>0) {
					$w *= $this->img_dpi / $info['set-dpi'];
					$h *= $this->img_dpi / $info['set-dpi'];
				}
				else if (preg_match('/(\d+)dpi/i', $properties['IMAGE-RESOLUTION'], $m)) {
					$dpi = $m[1]; 
					if ($dpi > 0) {
						$w *= $this->img_dpi / $dpi;
						$h *= $this->img_dpi / $dpi;
					}
				}
			}
		}
		// IF WIDTH OR HEIGHT SPECIFIED
		if($w==0)	$w=$h*$info['w']/$info['h'];
		if($h==0)	$h=$w*$info['h']/$info['w'];
		// Resize to maximum dimensions of page
		$maxWidth = $this->blk[$this->blklvl]['inner_width'];
   		$maxHeight = $this->h - ($this->tMargin + $this->bMargin + 10) ;
		if ($this->fullImageHeight) { $maxHeight = $this->fullImageHeight; }
		if ($w + $extrawidth > $maxWidth ) {
			$w = $maxWidth - $extrawidth;
			$h=$w*$info['h']/$info['w'];
		}
		if ($h + $extraheight > $maxHeight ) {
			$h = $maxHeight - $extraheight;
			$w=$h*$info['w']/$info['h'];
		}
		$height = $h + $extraheight;
		$width = $w + $extrawidth;
		$objattr['type'] = 'image';
		$objattr['itype'] = $info['type'];
		$objattr['orig_h'] = $info['h'];
		$objattr['orig_w'] = $info['w'];
/*-- IMAGES-WMF --*/
		if ($info['type']=='wmf') {
			$objattr['wmf_x'] = $info['x'];
			$objattr['wmf_y'] = $info['y'];
		}
		else 
/*-- END IMAGES-WMF --*/
		if ($info['type']=='svg') {
			$objattr['wmf_x'] = $info['x'];
			$objattr['wmf_y'] = $info['y'];
		}
		$objattr['height'] = $h + $extraheight;
		$objattr['width'] = $w + $extrawidth;

		$objattr['image_height'] = $h;
		$objattr['image_width'] = $w;
		$objattr['ID'] = $info['i'];
		$texto = 'X';
		if ($this->useActiveForms) {
			if (isset($attr['ONCLICK'])) { $objattr['onClick'] = $attr['ONCLICK']; }
			$objattr['type'] = 'input';
			$type = 'IMAGE';
		}
		break;
	}
/*-- END IMAGES-CORE --*/

	   case 'BUTTON': // Draw a button
	   case 'SUBMIT': 
	   case 'RESET': 
			$type = strtoupper($attr['TYPE']);
			if ($type=='IMAGE') { $type = 'BUTTON'; } // src path not found
			if(isset($attr['NOPRINT'])) { $objattr['noprint'] = true; }
			if (!isset($attr['VALUE'])) {
				$objattr['value'] = ucfirst(strtolower($type));
			}

			$texto = " " . $objattr['value'] . " ";
			$width = $this->GetStringWidth($texto) + ($this->form->form_element_spacing['button']['outer']['h']*2)+($this->form->form_element_spacing['button']['inner']['h']*2);
			$height = $this->FontSize + ($this->form->form_element_spacing['button']['outer']['v']*2)+($this->form->form_element_spacing['button']['inner']['v']*2);
			if ($this->useActiveForms) {
				if (isset($attr['ONCLICK'])) { $objattr['onClick'] = $attr['ONCLICK']; }
			}
			break;

	   case 'PASSWORD':
	   case 'TEXT': 
	   default:
                if ($type == '') { $type = 'TEXT'; }
		    if(strtoupper($attr['TYPE'])=='PASSWORD') { $type = 'PASSWORD'; }
                if (isset($attr['VALUE'])) {
			if ($type == 'PASSWORD') {
                    $num_stars = mb_strlen($attr['VALUE'],$this->mb_enc );
                    $texto = str_repeat('*',$num_stars);
			}
			else { $texto = $attr['VALUE']; }
                }
		    $xw = ($this->form->form_element_spacing['input']['outer']['h']*2)+($this->form->form_element_spacing['input']['inner']['h']*2);
		    $xh = ($this->form->form_element_spacing['input']['outer']['v']*2)+($this->form->form_element_spacing['input']['inner']['v']*2);
		    if ($w) { $width = $w + $xw; } 
		    else { $width = (20 * $spacesize) + $xw; }	// Default width in chars
                if (isset($attr['SIZE']) and ctype_digit($attr['SIZE']) ) $width = ($attr['SIZE'] * $spacesize) + $xw;
		    $height = $this->FontSize + $xh;
                if (isset($attr['MAXLENGTH']) and ctype_digit($attr['MAXLENGTH']) ) $objattr['maxlength'] = $attr['MAXLENGTH'];
		    if ($this->useActiveForms) {
			if (isset($attr['ONCALCULATE'])) { $objattr['onCalculate'] = $attr['ONCALCULATE']; }
			else if (isset($attr['ONCHANGE'])) { $objattr['onCalculate'] = $attr['ONCHANGE']; }
			if (isset($attr['ONVALIDATE'])) { $objattr['onValidate'] = $attr['ONVALIDATE']; }
			if (isset($attr['ONKEYSTROKE'])) { $objattr['onKeystroke'] = $attr['ONKEYSTROKE']; }
			if (isset($attr['ONFORMAT'])) { $objattr['onFormat'] = $attr['ONFORMAT']; }
		    }
                break;
	}

	$objattr['subtype'] = $type;
	$objattr['text'] = $texto;
	$objattr['width'] = $width;
	$objattr['height'] = $height;
	$e = "\xbb\xa4\xactype=input,objattr=".serialize($objattr)."\xbb\xa4\xac";

	// Clear properties - tidy up
	$properties = array();

/*-- TABLES --*/
	// Output it to buffers
	if ($this->tableLevel) {
		$this->_saveCellTextBuffer($e, $this->HREF);
		$this->cell[$this->row][$this->col]['s'] += $objattr['width'] ;

	}
	else {
/*-- END TABLES --*/
		$this->_saveTextBuffer($e, $this->HREF);
	}	// *TABLES*

	if ($this->InlineProperties[$tag]) { $this->restoreInlineProperties($this->InlineProperties[$tag]); }
	unset($this->InlineProperties[$tag]);

	break;	// END of INPUT
/*-- END FORMS --*/


	// *********** GRAPH  ********************
     case 'JPGRAPH':
	if (!$this->useGraphs) { break; }
	if ($attr['TABLE']) { $gid = strtoupper($attr['TABLE']); }
	else { $gid = '0'; }
	if (!is_array($this->graphs[$gid]) || count($this->graphs[$gid])==0 ) { break; }
	include_once(_MPDF_PATH.'graph.php');
	$this->graphs[$gid]['attr'] = $attr;


	if (isset($this->graphs[$gid]['attr']['WIDTH']) && $this->graphs[$gid]['attr']['WIDTH']) { 
		$this->graphs[$gid]['attr']['cWIDTH']=$this->ConvertSize($this->graphs[$gid]['attr']['WIDTH'],$pgwidth); 
	}	// mm
	if (isset($this->graphs[$gid]['attr']['HEIGHT']) && $this->graphs[$gid]['attr']['HEIGHT']) { 
		$this->graphs[$gid]['attr']['cHEIGHT']=$this->ConvertSize($this->graphs[$gid]['attr']['HEIGHT'],$pgwidth); 
	}

	$graph_img = print_graph($this->graphs[$gid],$this->blk[$this->blklvl]['inner_width']);
	if ($graph_img) { 
		if(isset($attr['ROTATE'])) {
		   if ($attr['ROTATE']==90 || $attr['ROTATE']==-90) {
			$tmpw = $graph_img['w'];
			$graph_img['w']= $graph_img['h'];
			$graph_img['h']= $tmpw;
		   }
		}
		$attr['SRC'] = $graph_img['file']; 
		$attr['WIDTH'] = $graph_img['w']; 
		$attr['HEIGHT'] = $graph_img['h']; 
	}
	else { break; }

	// *********** IMAGE  ********************
/*-- IMAGES-CORE --*/
    case 'IMG':
	if ($this->progressBar) { $this->UpdateProgressBar(1,'','IMG'); }	// *PROGRESS-BAR*
	$objattr = array();
		$objattr['margin_top'] = 0;
		$objattr['margin_bottom'] = 0;
		$objattr['margin_left'] = 0;
		$objattr['margin_right'] = 0;
		$objattr['padding_top'] = 0;
		$objattr['padding_bottom'] = 0;
		$objattr['padding_left'] = 0;
		$objattr['padding_right'] = 0;
		$objattr['width'] = 0;
		$objattr['height'] = 0;
		$objattr['border_top']['w'] = 0;
		$objattr['border_bottom']['w'] = 0;
		$objattr['border_left']['w'] = 0;
		$objattr['border_right']['w'] = 0;
	if(isset($attr['SRC']))	{
     		$srcpath = $attr['SRC'];
		$orig_srcpath = $attr['ORIG_SRC'];
		$properties = $this->cssmgr->MergeCSS('',$tag,$attr);
		if(isset($properties ['DISPLAY']) && strtolower($properties ['DISPLAY'])=='none') { 
			return; 
		}
		// mPDF 5.6.01  - LAYERS
		if (isset($properties['Z-INDEX']) && $this->currentlayer==0) {
			$v = intval($properties['Z-INDEX']); 
			if ($v > 0) {
				$objattr['z-index'] = $v; 
			}
		}

		$objattr['visibility'] = 'visible'; 
		if (isset($properties['VISIBILITY'])) {
			$v = strtolower($properties['VISIBILITY']);
			if (($v == 'hidden' || $v == 'printonly' || $v == 'screenonly') && $this->visibility=='visible') { 
				$objattr['visibility'] = $v; 
			}
		}

		// VSPACE and HSPACE converted to margins in MergeCSS
		if (isset($properties['MARGIN-TOP'])) { $objattr['margin_top']=$this->ConvertSize($properties['MARGIN-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if (isset($properties['MARGIN-BOTTOM'])) { $objattr['margin_bottom'] = $this->ConvertSize($properties['MARGIN-BOTTOM'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if (isset($properties['MARGIN-LEFT'])) { $objattr['margin_left'] = $this->ConvertSize($properties['MARGIN-LEFT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if (isset($properties['MARGIN-RIGHT'])) { $objattr['margin_right'] = $this->ConvertSize($properties['MARGIN-RIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }

		if (isset($properties['PADDING-TOP'])) { $objattr['padding_top']=$this->ConvertSize($properties['PADDING-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if (isset($properties['PADDING-BOTTOM'])) { $objattr['padding_bottom'] = $this->ConvertSize($properties['PADDING-BOTTOM'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if (isset($properties['PADDING-LEFT'])) { $objattr['padding_left'] = $this->ConvertSize($properties['PADDING-LEFT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if (isset($properties['PADDING-RIGHT'])) { $objattr['padding_right'] = $this->ConvertSize($properties['PADDING-RIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }

		if (isset($properties['BORDER-TOP'])) { $objattr['border_top'] = $this->border_details($properties['BORDER-TOP']); }
		if (isset($properties['BORDER-BOTTOM'])) { $objattr['border_bottom'] = $this->border_details($properties['BORDER-BOTTOM']); }
		if (isset($properties['BORDER-LEFT'])) { $objattr['border_left'] = $this->border_details($properties['BORDER-LEFT']); }
		if (isset($properties['BORDER-RIGHT'])) { $objattr['border_right'] = $this->border_details($properties['BORDER-RIGHT']); }

		if (isset($properties['VERTICAL-ALIGN'])) { $objattr['vertical-align'] = $align[strtolower($properties['VERTICAL-ALIGN'])]; }
		$w = 0;
		$h = 0;
		if(isset($properties['WIDTH'])) $w = $this->ConvertSize($properties['WIDTH'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
		else if(isset($attr['WIDTH'])) $w = $this->ConvertSize($attr['WIDTH'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
		if(isset($properties['HEIGHT'])) $h = $this->ConvertSize($properties['HEIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
		else if(isset($attr['HEIGHT'])) $h = $this->ConvertSize($attr['HEIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
		// mPDF 5.5.15		// mPDF 5.6.60
		$maxw=$maxh=$minw=$minh=false;
		if(isset($properties['MAX-WIDTH'])) $maxw = $this->ConvertSize($properties['MAX-WIDTH'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
		else if(isset($attr['MAX-WIDTH'])) $maxw = $this->ConvertSize($attr['MAX-WIDTH'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
		if(isset($properties['MAX-HEIGHT'])) $maxh = $this->ConvertSize($properties['MAX-HEIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
		else if(isset($attr['MAX-HEIGHT'])) $maxh = $this->ConvertSize($attr['MAX-HEIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
		if(isset($properties['MIN-WIDTH'])) $minw = $this->ConvertSize($properties['MIN-WIDTH'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
		else if(isset($attr['MIN-WIDTH'])) $minw = $this->ConvertSize($attr['MIN-WIDTH'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
		if(isset($properties['MIN-HEIGHT'])) $minh = $this->ConvertSize($properties['MIN-HEIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
		else if(isset($attr['MIN-HEIGHT'])) $minh = $this->ConvertSize($attr['MIN-HEIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);

		if (isset($properties['OPACITY']) && $properties['OPACITY'] > 0 && $properties['OPACITY'] <= 1) { $objattr['opacity'] = $properties['OPACITY']; }
		if ($this->HREF) {
			if (strpos($this->HREF,".") === false && strpos($this->HREF,"@") !== 0) {
				$href = $this->HREF;
				while(array_key_exists($href,$this->internallink)) $href="#".$href;
	    			$this->internallink[$href] = $this->AddLink();
				$objattr['link'] = $this->internallink[$href];
			}
			else { $objattr['link'] = $this->HREF; }
		}
		$extraheight = $objattr['padding_top'] + $objattr['padding_bottom'] + $objattr['margin_top'] + $objattr['margin_bottom'] + $objattr['border_top']['w'] + $objattr['border_bottom']['w'];
		$extrawidth = $objattr['padding_left'] + $objattr['padding_right'] + $objattr['margin_left'] + $objattr['margin_right'] + $objattr['border_left']['w'] + $objattr['border_right']['w'];

/*-- BACKGROUNDS --*/
		if(isset($properties['GRADIENT-MASK']) && preg_match('/(-moz-)*(repeating-)*(linear|radial)-gradient/',$properties['GRADIENT-MASK'])) {
	     		$objattr['GRADIENT-MASK'] = $properties['GRADIENT-MASK'];
		}
/*-- END BACKGROUNDS --*/

		// Image file
		$info=$this->_getImage($srcpath, true, true, $orig_srcpath);
		if(!$info) {
			$info = $this->_getImage($this->noImageFile);
			if ($info) { 
				$srcpath = $this->noImageFile; 
				$w = ($info['w'] * (25.4/$this->dpi)); 
				$h = ($info['h'] * (25.4/$this->dpi));
			}
		}
		if(!$info) break;

		if(isset($attr['ROTATE'])) { $image_orientation = $attr['ROTATE']; }
		else if(isset($properties['IMAGE-ORIENTATION'])) { $image_orientation = $properties['IMAGE-ORIENTATION']; }
		else { $image_orientation = 0; }
		if($image_orientation) {
		   if ($image_orientation==90 || $image_orientation==-90 || $image_orientation==270) {
			$tmpw = $info['w'];
			$info['w'] = $info['h'];
			$info['h'] = $tmpw;
		   }
		   $objattr['ROTATE'] = $image_orientation;
		}

		$objattr['file'] = $srcpath;
		//Default width and height calculation if needed
		if($w==0 and $h==0) {
/*-- IMAGES-WMF --*/
      	      if ($info['type']=='wmf') { 
				// WMF units are twips (1/20pt)
				// divide by 20 to get points
				// divide by k to get user units
				$w = abs($info['w'])/(20*_MPDFK);
				$h = abs($info['h']) / (20*_MPDFK);
			}
			else 
/*-- END IMAGES-WMF --*/
      	      if ($info['type']=='svg') { 
				// SVG units are pixels
				$w = abs($info['w'])/_MPDFK;
				$h = abs($info['h'])/_MPDFK;
			}
			else {
				//Put image at default image dpi
				$w=($info['w']/_MPDFK) * (72/$this->img_dpi);
				$h=($info['h']/_MPDFK) * (72/$this->img_dpi);
			}
			if (isset($properties['IMAGE-RESOLUTION'])) { 
				if (preg_match('/from-image/i', $properties['IMAGE-RESOLUTION']) && isset($info['set-dpi']) && $info['set-dpi']>0) {
					$w *= $this->img_dpi / $info['set-dpi'];
					$h *= $this->img_dpi / $info['set-dpi'];
				}
				else if (preg_match('/(\d+)dpi/i', $properties['IMAGE-RESOLUTION'], $m)) {
					$dpi = $m[1]; 
					if ($dpi > 0) {
						$w *= $this->img_dpi / $dpi;
						$h *= $this->img_dpi / $dpi;
					}
				}
			}
		}
		// IF WIDTH OR HEIGHT SPECIFIED
		if($w==0) $w=abs($h*$info['w']/$info['h']); 
		if($h==0) $h=abs($w*$info['h']/$info['w']);

		// mPDF 5.5.15
		if ($minw && $w<$minw) { $w = $minw; $h=abs($w*$info['h']/$info['w']); }
		if ($maxw && $w>$maxw) { $w = $maxw; $h=abs($w*$info['h']/$info['w']); }
		if ($minh && $h<$minh) { $h = $minh; $w=abs($h*$info['w']/$info['h']); }
		if ($maxh && $h>$maxh) { $h = $maxh; $w=abs($h*$info['w']/$info['h']); }

		// Resize to maximum dimensions of page
		$maxWidth = $this->blk[$this->blklvl]['inner_width'];
   		$maxHeight = $this->h - ($this->tMargin + $this->bMargin + 1) ;
		if ($this->fullImageHeight) { $maxHeight = $this->fullImageHeight; }
		if ($w + $extrawidth > $maxWidth ) {
			$w = $maxWidth - $extrawidth;
			$h=abs($w*$info['h']/$info['w']);
		}

		if ($h + $extraheight > $maxHeight ) {
			$h = $maxHeight - $extraheight;
			$w=abs($h*$info['w']/$info['h']);
		}
		$objattr['type'] = 'image';
		$objattr['itype'] = $info['type'];

		$objattr['orig_h'] = $info['h'];
		$objattr['orig_w'] = $info['w'];
/*-- IMAGES-WMF --*/
		if ($info['type']=='wmf') {
			$objattr['wmf_x'] = $info['x'];
			$objattr['wmf_y'] = $info['y'];
		}
		else 
/*-- END IMAGES-WMF --*/
		if ($info['type']=='svg') {
			$objattr['wmf_x'] = $info['x'];
			$objattr['wmf_y'] = $info['y'];
		}
		$objattr['height'] = $h + $extraheight;
		$objattr['width'] = $w + $extrawidth;
		$objattr['image_height'] = $h;
		$objattr['image_width'] = $w;
/*-- CSS-IMAGE-FLOAT --*/
		if (!$this->ColActive && !$this->tableLevel && !$this->listlvl && !$this->kwt && !$this->keep_block_together) {
		  if (isset($properties['FLOAT']) && (strtoupper($properties['FLOAT']) == 'RIGHT' || strtoupper($properties['FLOAT']) == 'LEFT')) {
			$objattr['float'] = substr(strtoupper($properties['FLOAT']),0,1);
		  }
		}
/*-- END CSS-IMAGE-FLOAT --*/

		$e = "\xbb\xa4\xactype=image,objattr=".serialize($objattr)."\xbb\xa4\xac";

		// Clear properties - tidy up
		$properties = array();

/*-- TABLES --*/
		// Output it to buffers
		if ($this->tableLevel) {
			$this->_saveCellTextBuffer($e, $this->HREF);
			$this->cell[$this->row][$this->col]['s'] += $objattr['width'] ;
		}
		else {
/*-- END TABLES --*/
			$this->_saveTextBuffer($e, $this->HREF);
		}	// *TABLES*
/*-- ANNOTATIONS --*/
		if ($this->title2annots && isset($attr['TITLE'])) {
			$objattr = array();
			$objattr['margin_top'] = 0;
			$objattr['margin_bottom'] = 0;
			$objattr['margin_left'] = 0;
			$objattr['margin_right'] = 0;
			$objattr['width'] = 0;
			$objattr['height'] = 0;
			$objattr['border_top']['w'] = 0;
			$objattr['border_bottom']['w'] = 0;
			$objattr['border_left']['w'] = 0;
			$objattr['border_right']['w'] = 0;
			$objattr['CONTENT'] = $attr['TITLE'];
			$objattr['type'] = 'annot';
			$objattr['POS-X'] = 0;
			$objattr['POS-Y'] = 0;
			$objattr['ICON'] = 'Comment';
			$objattr['AUTHOR'] = '';
			$objattr['SUBJECT'] = '';
			$objattr['OPACITY'] = $this->annotOpacity; 
			$objattr['COLOR'] = $this->ConvertColor('yellow');
			$e = "\xbb\xa4\xactype=annot,objattr=".serialize($objattr)."\xbb\xa4\xac";
			if($this->tableLevel) {	// *TABLES*
				$this->cell[$this->row][$this->col]['textbuffer'][] = array($e);	// *TABLES*
			}	// *TABLES*
			else  {	// *TABLES*
				$this->textbuffer[] = array($e);
			}	// *TABLES*
		}
/*-- END ANNOTATIONS --*/
	}
	break;
/*-- END IMAGES-CORE --*/


	// *********** CIRCULAR TEXT = TEXTCIRCLE  ********************
    case 'TEXTCIRCLE':
		$objattr = array();
		$objattr['margin_top'] = 0;
		$objattr['margin_bottom'] = 0;
		$objattr['margin_left'] = 0;
		$objattr['margin_right'] = 0;
		$objattr['padding_top'] = 0;
		$objattr['padding_bottom'] = 0;
		$objattr['padding_left'] = 0;
		$objattr['padding_right'] = 0;
		$objattr['width'] = 0;
		$objattr['height'] = 0;
		$objattr['border_top']['w'] = 0;
		$objattr['border_bottom']['w'] = 0;
		$objattr['border_left']['w'] = 0;
		$objattr['border_right']['w'] = 0;
		$objattr['top-text'] = '';
		$objattr['bottom-text'] = '';
		$objattr['r'] = 20;	// radius (default value here for safety)
		$objattr['space-width'] = 120;
		$objattr['char-width'] = 100;

		$this->InlineProperties[$tag] = $this->saveInlineProperties();
		$properties = $this->cssmgr->MergeCSS('INLINE',$tag,$attr);

		if(isset($properties ['DISPLAY']) && strtolower($properties ['DISPLAY'])=='none') { 
			return; 
		}
		if (isset($attr['R'])) { $objattr['r']=$this->ConvertSize($attr['R'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if(isset($attr['TOP-TEXT'])) { 
			$objattr['top-text'] = strcode2utf($attr['TOP-TEXT']);
			$objattr['top-text'] = $this->lesser_entity_decode($objattr['top-text']);
			if ($this->onlyCoreFonts)
				$objattr['top-text'] = mb_convert_encoding($objattr['top-text'], $this->mb_enc,'UTF-8'); 
		}
		if(isset($attr['BOTTOM-TEXT'])) { 
			$objattr['bottom-text'] = strcode2utf($attr['BOTTOM-TEXT']);
			$objattr['bottom-text'] = $this->lesser_entity_decode($objattr['bottom-text']);
			if ($this->onlyCoreFonts)
				$objattr['bottom-text'] = mb_convert_encoding($objattr['bottom-text'], $this->mb_enc,'UTF-8'); 
		}
		if(isset($attr['SPACE-WIDTH']) && $attr['SPACE-WIDTH']) { $objattr['space-width'] = $attr['SPACE-WIDTH']; }
		if(isset($attr['CHAR-WIDTH']) && $attr['CHAR-WIDTH']) { $objattr['char-width'] = $attr['CHAR-WIDTH']; }

		// VISIBILITY
		$objattr['visibility'] = 'visible'; 
		if (isset($properties['VISIBILITY'])) {
			$v = strtolower($properties['VISIBILITY']);
			if (($v == 'hidden' || $v == 'printonly' || $v == 'screenonly') && $this->visibility=='visible') { 
				$objattr['visibility'] = $v; 
			}
		}
		// mPDF 5.5.23
		if (isset($properties['FONT-SIZE'])) { 
		  if (strtolower($properties['FONT-SIZE'])=='auto') {
			if ($objattr['top-text'] && $objattr['bottom-text']) {
				$objattr['fontsize'] = -2;
			}
			else {
				$objattr['fontsize'] = -1;
			}
		  }
		  else {
			$mmsize = $this->ConvertSize($properties['FONT-SIZE'],($this->default_font_size/_MPDFK));
  			$this->SetFontSize($mmsize*_MPDFK,false);
			$objattr['fontsize'] = $this->FontSizePt;
		  }
		}
		// mPDF 5.5.23
		if(isset($attr['DIVIDER'])) {
			$objattr['divider'] = strcode2utf($attr['DIVIDER']);
			$objattr['divider'] = $this->lesser_entity_decode($objattr['divider']);
			if ($this->onlyCoreFonts)
				$objattr['divider'] = mb_convert_encoding($objattr['divider'], $this->mb_enc,'UTF-8'); 

		}

		if (isset($properties['COLOR'])) { $objattr['color'] = $this->ConvertColor($properties['COLOR']); }

		$objattr['fontstyle'] = '';
		if (isset($properties['FONT-WEIGHT'])) {
			if (strtoupper($properties['FONT-WEIGHT']) == 'BOLD')	{ $objattr['fontstyle'] .= 'B'; }
		}
		if (isset($properties['FONT-STYLE'])) {
			if (strtoupper($properties['FONT-STYLE']) == 'ITALIC') { $objattr['fontstyle'] .= 'I'; }
		}

		if (isset($properties['FONT-FAMILY'])) { 
			$this->SetFont($properties['FONT-FAMILY'],$this->FontStyle,0,false);
		}
		$objattr['fontfamily'] = $this->FontFamily;

		// VSPACE and HSPACE converted to margins in MergeCSS
		if (isset($properties['MARGIN-TOP'])) { $objattr['margin_top']=$this->ConvertSize($properties['MARGIN-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if (isset($properties['MARGIN-BOTTOM'])) { $objattr['margin_bottom'] = $this->ConvertSize($properties['MARGIN-BOTTOM'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if (isset($properties['MARGIN-LEFT'])) { $objattr['margin_left'] = $this->ConvertSize($properties['MARGIN-LEFT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if (isset($properties['MARGIN-RIGHT'])) { $objattr['margin_right'] = $this->ConvertSize($properties['MARGIN-RIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }

		if (isset($properties['PADDING-TOP'])) { $objattr['padding_top']=$this->ConvertSize($properties['PADDING-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if (isset($properties['PADDING-BOTTOM'])) { $objattr['padding_bottom'] = $this->ConvertSize($properties['PADDING-BOTTOM'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if (isset($properties['PADDING-LEFT'])) { $objattr['padding_left'] = $this->ConvertSize($properties['PADDING-LEFT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if (isset($properties['PADDING-RIGHT'])) { $objattr['padding_right'] = $this->ConvertSize($properties['PADDING-RIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }

		if (isset($properties['BORDER-TOP'])) { $objattr['border_top'] = $this->border_details($properties['BORDER-TOP']); }
		if (isset($properties['BORDER-BOTTOM'])) { $objattr['border_bottom'] = $this->border_details($properties['BORDER-BOTTOM']); }
		if (isset($properties['BORDER-LEFT'])) { $objattr['border_left'] = $this->border_details($properties['BORDER-LEFT']); }
		if (isset($properties['BORDER-RIGHT'])) { $objattr['border_right'] = $this->border_details($properties['BORDER-RIGHT']); }

		if (isset($properties['OPACITY']) && $properties['OPACITY'] > 0 && $properties['OPACITY'] <= 1) { $objattr['opacity'] = $properties['OPACITY']; }
		if (isset($properties['BACKGROUND-COLOR']) && $properties['BACKGROUND-COLOR'] != '') { $objattr['bgcolor'] = $this->ConvertColor($properties['BACKGROUND-COLOR']); }
		else { $objattr['bgcolor'] = false; }
		if ($this->HREF) {
			if (strpos($this->HREF,".") === false && strpos($this->HREF,"@") !== 0) {
				$href = $this->HREF;
				while(array_key_exists($href,$this->internallink)) $href="#".$href;
	    			$this->internallink[$href] = $this->AddLink();
				$objattr['link'] = $this->internallink[$href];
			}
			else { $objattr['link'] = $this->HREF; }
		}
		$extraheight = $objattr['padding_top'] + $objattr['padding_bottom'] + $objattr['margin_top'] + $objattr['margin_bottom'] + $objattr['border_top']['w'] + $objattr['border_bottom']['w'];
		$extrawidth = $objattr['padding_left'] + $objattr['padding_right'] + $objattr['margin_left'] + $objattr['margin_right'] + $objattr['border_left']['w'] + $objattr['border_right']['w'];


		$w = $objattr['r']*2;
		$h = $w;
		$objattr['height'] = $h + $extraheight;
		$objattr['width'] = $w + $extrawidth;
		$objattr['type'] = 'textcircle';

		$e = "\xbb\xa4\xactype=image,objattr=".serialize($objattr)."\xbb\xa4\xac";

		// Clear properties - tidy up
		$properties = array();

/*-- TABLES --*/
		// Output it to buffers
		if ($this->tableLevel) {
			$this->_saveCellTextBuffer($e, $this->HREF);
			$this->cell[$this->row][$this->col]['s'] += $objattr['width'] ;
		}
		else {
/*-- END TABLES --*/
			$this->_saveTextBuffer($e, $this->HREF);
		}	// *TABLES*

		if ($this->InlineProperties[$tag]) { $this->restoreInlineProperties($this->InlineProperties[$tag]); }
		unset($this->InlineProperties[$tag]);

		break;


/*-- TABLES --*/

    case 'TABLE': // TABLE-BEGIN
	$this->tdbegin = false;
	$this->lastoptionaltag = '';
	// Disable vertical justification in columns
	if ($this->ColActive) { $this->colvAlign = ''; }	// *COLUMNS*
	if ($this->lastblocklevelchange == 1) { $blockstate = 1; }	// Top margins/padding only
	else if ($this->lastblocklevelchange < 1) { $blockstate = 0; }	// NO margins/padding
	// called from block after new div e.g. <div> ... <table> ...    Outputs block top margin/border and padding
	if (count($this->textbuffer) == 0 && $this->lastblocklevelchange == 1 && !$this->tableLevel && !$this->kwt) {
		$this->newFlowingBlock( $this->blk[$this->blklvl]['width'],$this->lineheight,'',false,false,1,true, $this->blk[$this->blklvl]['direction']);
		$this->finishFlowingBlock(true);	// true = END of flowing block
	}
	else if (!$this->tableLevel && count($this->textbuffer)) { $this->printbuffer($this->textbuffer,$blockstate); }

	$this->textbuffer=array();
	$this->lastblocklevelchange = -1;
	if ($this->tableLevel) {	// i.e. now a nested table coming...
		// Save current level table
		// mPDF 5.4.10
		$this->cell['PARENTCELL'] = $this->saveInlineProperties();
		$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['baseProperties']= $this->base_table_properties;
		$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['cells'] = $this->cell;
		$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['currrow'] = $this->row;
		$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['currcol'] = $this->col;
	}
	$this->tableLevel++;
	$this->cssmgr->tbCSSlvl++;

	if ($this->tableLevel>1) {	// inherit table properties from cell in which nested
		$this->base_table_properties['FONT-KERNING'] = $this->kerning ;
		$this->base_table_properties['LETTER-SPACING'] = $this->lSpacingCSS ;
		$this->base_table_properties['WORD-SPACING'] = $this->wSpacingCSS ;
	}

	if (isset($this->tbctr[$this->tableLevel])) { $this->tbctr[$this->tableLevel]++; }
	else { $this->tbctr[$this->tableLevel] = 1; }

	$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['level'] = $this->tableLevel;
	$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['levelid'] = $this->tbctr[$this->tableLevel];

	if ($this->tableLevel > $this->innermostTableLevel) { $this->innermostTableLevel = $this->tableLevel; }
	if ($this->tableLevel > 1) { 
		$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['nestedpos'] = array($this->row,$this->col,$this->tbctr[($this->tableLevel-1)]); 
	}
	//++++++++++++++++++++++++++++

	$this->cell = array();
	$this->col=-1; //int
	$this->row=-1; //int
	$table = &$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]];
 
	// New table - any level
	if ($this->cacheTables) {
		$this->packTableData = true;	// required for cacheTables
		$this->simpleTables = false;  // Cannot co-exist with cacheTables
		$table['cache'] = _MPDF_TEMP_PATH.'_tempTblCache'.RAND(1,1000000).'.dat';
		$fh = fopen($table['cache'] , "wb") or $this->Error("When using cacheTables, you must have read/write access to cache files (".$table['cache'] .")");
		fwrite($fh, "\x00");
		fclose($fh);
		$table['ptr'] = 1 ;	// Must not be 0
	}

	$table['direction'] = $this->directionality;
	$table['bgcolor'] = false;
	$table['va'] = false;
	$table['txta'] = false;
	$table['topntail'] = false;
	$table['thead-underline'] = false;
	$table['border'] = false;
	$table['border_details']['R']['w'] = 0;
	$table['border_details']['L']['w'] = 0;
	$table['border_details']['T']['w'] = 0;
	$table['border_details']['B']['w'] = 0;
	$table['border_details']['R']['style'] = '';
	$table['border_details']['L']['style'] = '';
	$table['border_details']['T']['style'] = '';
	$table['border_details']['B']['style'] = '';
	$table['max_cell_border_width']['R'] = 0;
	$table['max_cell_border_width']['L'] = 0;
	$table['max_cell_border_width']['T'] = 0;
	$table['max_cell_border_width']['B'] = 0;
	$table['padding']['L'] = false;
	$table['padding']['R'] = false;
	$table['padding']['T'] = false;
	$table['padding']['B'] = false;
	$table['margin']['L'] = false;
	$table['margin']['R'] = false;
	$table['margin']['T'] = false;
	$table['margin']['B'] = false;
	$table['a'] = false;
	$table['border_spacing_H'] = false;
	$table['border_spacing_V'] = false;
	$table['decimal_align'] = false;	// mPDF 5.6.13
	$this->Reset();
	$this->InlineProperties = array();
	$this->spanlvl = 0;
	$table['nc'] = $table['nr'] = 0;
	$this->tablethead = 0;
	$this->tabletfoot = 0;
	$this->tabletheadjustfinished = false;


	if ($this->blockjustfinished && !count($this->textbuffer) && $this->y != $this->tMargin && $this->collapseBlockMargins && $this->tableLevel==1) { $lastbottommargin = $this->lastblockbottommargin; }
	else { $lastbottommargin = 0; }
	$this->lastblockbottommargin = 0;
	$this->blockjustfinished=false;

	if ($this->tableLevel==1) { 
		$this->tableCJK = false;
		$this->table_lineheight = $this->normalLineheight;
		$table['headernrows'] = 0;
		$table['footernrows'] = 0; 
		$this->base_table_properties = array();
	}

	// ADDED CSS FUNCIONS FOR TABLE 
	if ($this->cssmgr->tbCSSlvl==1) {
		$properties = $this->cssmgr->MergeCSS('TOPTABLE',$tag,$attr);
	}
	else {
		$properties = $this->cssmgr->MergeCSS('TABLE',$tag,$attr);
	}
	$w = '';
	if (isset($properties['WIDTH'])) { $w = $properties['WIDTH']; }
	else if (isset($attr['WIDTH']) && $attr['WIDTH']) { $w = $attr['WIDTH']; }


	if(isset($properties['DIRECTION']) && $properties['DIRECTION']) { $table['direction'] = strtolower($properties['DIRECTION']); }
	else if(isset($attr['DIR']) && $attr['DIR']) { $table['direction'] = strtolower($attr['DIR']); }
	else if (!isset($table['direction'])){ $table['direction'] = $this->blk[$this->blklvl]['direction']; }

	if (isset($properties['BACKGROUND-COLOR'])) { $table['bgcolor'][-1] = $properties['BACKGROUND-COLOR'];	}
	else if (isset($properties['BACKGROUND'])) { $table['bgcolor'][-1] = $properties['BACKGROUND'];	}
	else if (isset($attr['BGCOLOR'])) { $table['bgcolor'][-1]	= $attr['BGCOLOR']; }
	if (isset($properties['VERTICAL-ALIGN'])) { $table['va'] = $align[strtolower($properties['VERTICAL-ALIGN'])]; }
	if (isset($properties['TEXT-ALIGN'])) { $table['txta'] = $align[strtolower($properties['TEXT-ALIGN'])]; }
	if (isset($attr['ALIGN'])) { $table['a']	= $align[strtolower($attr['ALIGN'])]; }
	if (!$table['a']) { 
		if ($table['direction'] == 'rtl' ) { $table['a'] = 'R'; }
		else { $table['a'] = 'L'; }
	}

	if (isset($properties['AUTOSIZE']) && $properties['AUTOSIZE'] && $this->tableLevel ==1)	{ 
		$this->shrink_this_table_to_fit = $properties['AUTOSIZE']; 
		if ($this->shrink_this_table_to_fit < 1) { $this->shrink_this_table_to_fit = 0; }
	}
	if (isset($properties['ROTATE']) && $properties['ROTATE'] && $this->tableLevel ==1)	{ 
		$this->table_rotate = $properties['ROTATE']; 
	}
	if (isset($properties['TOPNTAIL'])) { $table['topntail'] = $properties['TOPNTAIL']; }
	if (isset($properties['THEAD-UNDERLINE'])) { $table['thead-underline'] = $properties['THEAD-UNDERLINE']; }

	if (isset($properties['BORDER'])) { 
		$bord = $this->border_details($properties['BORDER']);
		if ($bord['s']) {
			$table['border'] = _BORDER_ALL;
			$table['border_details']['R'] = $bord;
			$table['border_details']['L'] = $bord;
			$table['border_details']['T'] = $bord;
			$table['border_details']['B'] = $bord;
		}
	}
	if (isset($properties['BORDER-RIGHT'])) { 
	  if ($table['direction'] == 'rtl') { 	// *RTL*
		$table['border_details']['R'] = $this->border_details($properties['BORDER-LEFT']);	// *RTL*
	  }	// *RTL*
	  else {	// *RTL*
		$table['border_details']['R'] = $this->border_details($properties['BORDER-RIGHT']);
	  }	// *RTL*
	  $this->setBorder($table['border'], _BORDER_RIGHT, $table['border_details']['R']['s']); 
	}
	if (isset($properties['BORDER-LEFT'])) { 
	  if ($table['direction'] == 'rtl') { 	// *RTL*
		$table['border_details']['L'] = $this->border_details($properties['BORDER-RIGHT']);	// *RTL*
	  }	// *RTL*
	  else {	// *RTL*
		$table['border_details']['L'] = $this->border_details($properties['BORDER-LEFT']);
	  }	// *RTL*
	  $this->setBorder($table['border'], _BORDER_LEFT, $table['border_details']['L']['s']); 
	}
	if (isset($properties['BORDER-BOTTOM'])) { 
		$table['border_details']['B'] = $this->border_details($properties['BORDER-BOTTOM']);
		$this->setBorder($table['border'], _BORDER_BOTTOM, $table['border_details']['B']['s']); 
	}
	if (isset($properties['BORDER-TOP'])) { 
		$table['border_details']['T'] = $this->border_details($properties['BORDER-TOP']);
		$this->setBorder($table['border'], _BORDER_TOP, $table['border_details']['T']['s']); 
	}
	if ($table['border']){
		  $this->table_border_css_set = 1;
	}
	else {
	  $this->table_border_css_set = 0;
	}

	if (isset($properties['FONT-FAMILY'])) { 
		$this->default_font = $properties['FONT-FAMILY'];
		$this->SetFont($this->default_font,'',0,false);
	}
	$this->base_table_properties['FONT-FAMILY'] = $this->FontFamily;	// mPDF 5.4.10

	if (isset($properties['FONT-SIZE'])) { 
	   // mPDF 5.4.10
	   if ($this->tableLevel>1) { $mmsize = $this->ConvertSize($properties['FONT-SIZE'], $this->base_table_properties['FONT-SIZE']); }
	   else { $mmsize = $this->ConvertSize($properties['FONT-SIZE'],$this->default_font_size/_MPDFK); }
	   if ($mmsize) {
		$this->default_font_size = $mmsize*(_MPDFK);
   		$this->SetFontSize($this->default_font_size,false);
	   }
	}
	$this->base_table_properties['FONT-SIZE'] = $this->FontSize.'mm';	// mPDF 5.4.10

	if (isset($properties['FONT-WEIGHT'])) {
		if (strtoupper($properties['FONT-WEIGHT']) == 'BOLD')	{ $this->base_table_properties['FONT-WEIGHT'] = 'BOLD'; }
	}
	if (isset($properties['FONT-STYLE'])) {
		if (strtoupper($properties['FONT-STYLE']) == 'ITALIC') { $this->base_table_properties['FONT-STYLE'] = 'ITALIC'; }
	}
	if (isset($properties['COLOR'])) {
		$this->base_table_properties['COLOR'] = $properties['COLOR'];
	}
	if (isset($properties['FONT-KERNING'])) {
		$this->base_table_properties['FONT-KERNING'] = $properties['FONT-KERNING'];
	}
	if (isset($properties['LETTER-SPACING'])) {
		$this->base_table_properties['LETTER-SPACING'] = $properties['LETTER-SPACING'];
	}
	if (isset($properties['WORD-SPACING'])) {
		$this->base_table_properties['WORD-SPACING'] = $properties['WORD-SPACING'];
	}

	if (isset($properties['PADDING-LEFT'])) { 
		$table['padding']['L'] = $this->ConvertSize($properties['PADDING-LEFT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
	}
	if (isset($properties['PADDING-RIGHT'])) { 
		$table['padding']['R'] = $this->ConvertSize($properties['PADDING-RIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
	}
	if (isset($properties['PADDING-TOP'])) { 
		$table['padding']['T'] = $this->ConvertSize($properties['PADDING-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
	}
	if (isset($properties['PADDING-BOTTOM'])) { 
		$table['padding']['B'] = $this->ConvertSize($properties['PADDING-BOTTOM'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
	}

	if (isset($properties['MARGIN-TOP'])) { 
		if ($lastbottommargin) { 
			$tmp = $this->ConvertSize($properties['MARGIN-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
			if ($tmp > $lastbottommargin) { $properties['MARGIN-TOP'] -= $lastbottommargin; }
			else { $properties['MARGIN-TOP'] = 0; }
		}
		$table['margin']['T'] = $this->ConvertSize($properties['MARGIN-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); 
	}

	if (isset($properties['MARGIN-BOTTOM'])) { 
		$table['margin']['B'] = $this->ConvertSize($properties['MARGIN-BOTTOM'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); 
	}
	if (isset($properties['MARGIN-LEFT'])) {
		$table['margin']['L'] = $this->ConvertSize($properties['MARGIN-LEFT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); 
	}

	if (isset($properties['MARGIN-RIGHT'])) {
		$table['margin']['R'] = $this->ConvertSize($properties['MARGIN-RIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); 
	}
	if (isset($properties['MARGIN-LEFT']) && isset($properties['MARGIN-RIGHT']) && strtolower($properties['MARGIN-LEFT'])=='auto' && strtolower($properties['MARGIN-RIGHT'])=='auto') { 
		$table['a'] = 'C'; 
	}
	else if (isset($properties['MARGIN-LEFT']) && strtolower($properties['MARGIN-LEFT'])=='auto') { 
		$table['a'] = 'R'; 
	}
	else if (isset($properties['MARGIN-RIGHT']) && strtolower($properties['MARGIN-RIGHT'])=='auto') { 
		$table['a'] = 'L'; 
	}

	if (isset($properties['LINE-HEIGHT']) && $this->tableLevel==1) { 
		$this->table_lineheight = $this->fixLineheight($properties['LINE-HEIGHT']);
		if (!$this->table_lineheight) { $this->table_lineheight = $this->normalLineheight; }
	}

	if (isset($properties['BORDER-COLLAPSE']) && strtoupper($properties['BORDER-COLLAPSE'])=='SEPARATE') { 
		$table['borders_separate'] = true; 
	}
	else { 
		$table['borders_separate'] = false; 
	}

	if (!$table['borders_separate']) { $table['border_spacing_H'] = $table['border_spacing_V'] = 0; }
	else if (isset($attr['CELLSPACING'])) { 
		$table['border_spacing_H'] = $table['border_spacing_V'] = $this->ConvertSize($attr['CELLSPACING'],$this->blk[$this->blklvl]['inner_width']); 
	}
	if (isset($properties['BORDER-SPACING-H'])) { 
		$table['border_spacing_H'] = $this->ConvertSize($properties['BORDER-SPACING-H'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); 
	}
	if (isset($properties['BORDER-SPACING-V'])) { 
		$table['border_spacing_V'] = $this->ConvertSize($properties['BORDER-SPACING-V'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); 
	}

	if (isset($properties['EMPTY-CELLS'])) { 
		$table['empty_cells'] = strtolower($properties['EMPTY-CELLS']); 	// 'hide'  or 'show'
	}
	else { $table['empty_cells'] = ''; } 

	if (isset($properties['PAGE-BREAK-INSIDE']) && strtoupper($properties['PAGE-BREAK-INSIDE'])=='AVOID' && $this->tableLevel==1 && !$this->writingHTMLfooter) {
		$this->table_keep_together = true; 
	}
	else if ($this->tableLevel==1) { 
		$this->table_keep_together = false; 
	}
	if (isset($properties['PAGE-BREAK-AFTER']) && $this->tableLevel==1) {
		$table['page_break_after'] = strtoupper($properties['PAGE-BREAK-AFTER']);
	}

/*-- BACKGROUNDS --*/
	if (isset($properties['BACKGROUND-GRADIENT']) && !$this->kwt && !$this->ColActive) { $table['gradient'] = $properties['BACKGROUND-GRADIENT']; }

	if (isset($properties['BACKGROUND-IMAGE']) && $properties['BACKGROUND-IMAGE'] && !$this->kwt && !$this->ColActive) {
		$ret = $this->SetBackground($properties, $currblk['inner_width']);
		if ($ret) { $table['background-image'] = $ret; }
	}
/*-- END BACKGROUNDS --*/

	if (isset($properties['OVERFLOW']))	{ 
		$table['overflow'] = strtolower($properties['OVERFLOW']); 	// 'hidden' 'wrap' or 'visible' or 'auto'
		if (($this->ColActive || $this->tableLevel>1) && $table['overflow']=='visible') { unset($table['overflow']); }
	}

	$properties = array();



	if (isset($attr['CELLPADDING'])) {
		$table['cell_padding'] = $attr['CELLPADDING'];
	}
	else {
		$table['cell_padding'] = false;
	}

	if (isset($attr['BORDER']) && $attr['BORDER']=='1') {	// mPDF 5.5.08
		$this->table_border_attr_set = 1;	// mPDF 5.5.08
		$bord = $this->border_details('#000000 1px solid');
		if ($bord['s']) {
			$table['border'] = _BORDER_ALL;
			$table['border_details']['R'] = $bord;
			$table['border_details']['L'] = $bord;
			$table['border_details']['T'] = $bord;
			$table['border_details']['B'] = $bord;
		}
	}
	else {
		$this->table_border_attr_set = 0;
	}

	if ($w) {
		$maxwidth = $this->blk[$this->blklvl]['inner_width'];
		if ($table['borders_separate']) { 
			$tblblw = $table['margin']['L'] + $table['margin']['R'] + $table['border_details']['L']['w']/2 + $table['border_details']['R']['w']/2;
		}
		else { 
			$tblblw = $table['margin']['L'] + $table['margin']['R'] + $table['max_cell_border_width']['L']/2 + $table['max_cell_border_width']['R']/2;
		}
		if (strpos($w,'%') && $this->tableLevel == 1 && !$this->ignore_table_percents ) { 
			// % needs to be of inner box without table margins etc.
			$maxwidth -= $tblblw ;
			$wmm = $this->ConvertSize($w,$maxwidth,$this->FontSize,false);
			$table['w'] = $wmm + $tblblw ;
		}
		if (strpos($w,'%') && $this->tableLevel > 1 && !$this->ignore_table_percents && $this->keep_table_proportions) { 
			$table['wpercent'] = $w + 0; 	// makes 80% -> 80
		}
		if (!strpos($w,'%') && !$this->ignore_table_widths ) {
			$wmm = $this->ConvertSize($w,$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
			$table['w'] = $wmm + $tblblw ;
		}
		if (!$this->keep_table_proportions) {
			if (isset($table['w']) && $table['w'] > $this->blk[$this->blklvl]['inner_width']) { $table['w'] = $this->blk[$this->blklvl]['inner_width']; }
		}
	}

	if (isset($attr['AUTOSIZE']) && $this->tableLevel==1)	{ 
		$this->shrink_this_table_to_fit = $attr['AUTOSIZE']; 
		if ($this->shrink_this_table_to_fit < 1) { $this->shrink_this_table_to_fit = 1; }
	}
	if (isset($attr['ROTATE']) && $this->tableLevel==1)	{ 
		$this->table_rotate = $attr['ROTATE']; 
	}

	//++++++++++++++++++++++++++++
	// keeping block together on one page
	// Autosize is now forced therefore keep block together disabled
	if ($this->keep_block_together) {
		$this->keep_block_together = 0;
		$this->printdivbuffer();
		$this->blk[$this->blklvl]['keep_block_together'] = 0;
	}
	if ($this->table_rotate) {
		$this->tbrot_Links = array();
		$this->tbrot_Annots = array();
		$this->tbrotForms = array();
		$this->tbrot_Reference = array();
		$this->tbrot_BMoutlines = array();
		$this->tbrot_toc = array();
	}

	if ($this->kwt) {
		if ($this->table_rotate) { $this->table_keep_together = true; }
		$this->kwt = false;
		$this->kwt_saved = true;
	}

	if ($this->tableLevel==1 && $this->useGraphs) { 
		if (isset($attr['ID']) && $attr['ID']) { $this->currentGraphId = strtoupper($attr['ID']); }
		else { $this->currentGraphId = '0'; }
		$this->graphs[$this->currentGraphId] = array();
	}
	//++++++++++++++++++++++++++++
	$this->plainCell_properties = array();
	unset($table);
	break;

    case 'THEAD':
	$this->lastoptionaltag = $tag; // Save current HTML specified optional endtag
	$this->cssmgr->tbCSSlvl++;
	$this->tablethead = 1;
	$this->tabletfoot = 0; 
	$properties = $this->cssmgr->MergeCSS('TABLE',$tag,$attr);
	if (isset($properties['FONT-WEIGHT'])) {
		if (strtoupper($properties['FONT-WEIGHT']) == 'BOLD')	{ $this->thead_font_weight = 'B'; }
		else { $this->thead_font_weight = ''; }
	}

	if (isset($properties['FONT-STYLE'])) {
		if (strtoupper($properties['FONT-STYLE']) == 'ITALIC') { $this->thead_font_style = 'I'; }
		else { $this->thead_font_style = ''; }
	}
	if (isset($properties['FONT-VARIANT'])) {
		if (strtoupper($properties['FONT-VARIANT']) == 'SMALL-CAPS') { $this->thead_font_smCaps = 'S'; }
		else { $this->thead_font_smCaps = ''; }
	}

	if (isset($properties['VERTICAL-ALIGN'])) {
		$this->thead_valign_default = $properties['VERTICAL-ALIGN'];
	}
	if (isset($properties['TEXT-ALIGN'])) {
		$this->thead_textalign_default = $properties['TEXT-ALIGN'];
	}
	$properties = array();
	break;

    case 'TFOOT':
	$this->lastoptionaltag = $tag; // Save current HTML specified optional endtag
	$this->cssmgr->tbCSSlvl++;
	$this->tabletfoot = 1; 
	$this->tablethead = 0;
	$properties = $this->cssmgr->MergeCSS('TABLE',$tag,$attr);
	if (isset($properties['FONT-WEIGHT'])) {
		if (strtoupper($properties['FONT-WEIGHT']) == 'BOLD')	{ $this->tfoot_font_weight = 'B'; }
		else { $this->tfoot_font_weight = ''; }
	}

	if (isset($properties['FONT-STYLE'])) {
		if (strtoupper($properties['FONT-STYLE']) == 'ITALIC') { $this->tfoot_font_style = 'I'; }
		else { $this->tfoot_font_style = ''; }
	}
	if (isset($properties['FONT-VARIANT'])) {	
		if (strtoupper($properties['FONT-VARIANT']) == 'SMALL-CAPS') { $this->tfoot_font_smCaps = 'S'; }
		else { $this->tfoot_font_smCaps = ''; }
	}

	if (isset($properties['VERTICAL-ALIGN'])) {
		$this->tfoot_valign_default = $properties['VERTICAL-ALIGN'];
	}
	if (isset($properties['TEXT-ALIGN'])) {
		$this->tfoot_textalign_default = $properties['TEXT-ALIGN'];
	}
	$properties = array();
	break;


    case 'TBODY':
	$this->tablethead = 0;
	$this->tabletfoot = 0;
	$this->lastoptionaltag = $tag; // Save current HTML specified optional endtag
	$this->cssmgr->tbCSSlvl++;
	$this->cssmgr->MergeCSS('TABLE',$tag,$attr);
	break;


    case 'TR':
	$this->lastoptionaltag = $tag; // Save current HTML specified optional endtag
	$this->cssmgr->tbCSSlvl++;
	$this->row++;
	$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['nr']++;
	$this->col = -1;
	$properties = $this->cssmgr->MergeCSS('TABLE',$tag,$attr);

	if (!$this->simpleTables && (!isset($this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['borders_separate']) || !$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['borders_separate'])) { 
		if (isset($properties['BORDER-LEFT']) && $properties['BORDER-LEFT']) { $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['trborder-left'][$this->row] = $properties['BORDER-LEFT']; }
		if (isset($properties['BORDER-RIGHT']) && $properties['BORDER-RIGHT']) { $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['trborder-right'][$this->row] = $properties['BORDER-RIGHT']; }
		if (isset($properties['BORDER-TOP']) && $properties['BORDER-TOP']) { $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['trborder-top'][$this->row] = $properties['BORDER-TOP']; }
		if (isset($properties['BORDER-BOTTOM']) && $properties['BORDER-BOTTOM']) { $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['trborder-bottom'][$this->row] = $properties['BORDER-BOTTOM']; }
	}

	if (isset($properties['BACKGROUND-COLOR'])) { $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['bgcolor'][$this->row] = $properties['BACKGROUND-COLOR']; }
	else if (isset($attr['BGCOLOR'])) $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['bgcolor'][$this->row] = $attr['BGCOLOR'];

/*-- BACKGROUNDS --*/
	if (isset($properties['BACKGROUND-GRADIENT']) && !$this->kwt && !$this->ColActive) { $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['trgradients'][$this->row] = $properties['BACKGROUND-GRADIENT']; }

	if (isset($properties['BACKGROUND-IMAGE']) && $properties['BACKGROUND-IMAGE'] && !$this->kwt && !$this->ColActive) {
		$ret = $this->SetBackground($properties, $currblk['inner_width']);
		if ($ret) { $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['trbackground-images'][$this->row] = $ret; }
	}
/*-- END BACKGROUNDS --*/


	if (isset($properties['TEXT-ROTATE'])) {
		$this->trow_text_rotate = $properties['TEXT-ROTATE'];
	}
	if (isset($attr['TEXT-ROTATE'])) $this->trow_text_rotate = $attr['TEXT-ROTATE'];

	if ($this->tablethead) { $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['is_thead'][$this->row] = true; }
	if ($this->tabletfoot) { $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['is_tfoot'][$this->row] = true; }
	$properties = array();
	break;


    case 'TH':
    case 'TD':
	$this->ignorefollowingspaces = true; 
	$this->lastoptionaltag = $tag; // Save current HTML specified optional endtag
	$this->cssmgr->tbCSSlvl++;
	$this->InlineProperties = array();
	$this->spanlvl = 0;
	$this->tdbegin = true;
	$this->col++;
	while (isset($this->cell[$this->row][$this->col])) { $this->col++; }

	//Update number column
	if ($this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['nc'] < $this->col+1) { $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['nc'] = $this->col+1; }

	$table = &$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]];

	$c = array('a' => false,
	'R' => false,
	'nowrap' => false,
	'bgcolor' => false,
	'padding' => array('L' => false,
		'R' => false,
		'T' => false,
		'B' => false
		)
	);

	if ($this->simpleTables && $this->row==0 && $this->col==0){
		$table['simple']['border'] = false;
		$table['simple']['border_details']['R']['w'] = 0;
		$table['simple']['border_details']['L']['w'] = 0;
		$table['simple']['border_details']['T']['w'] = 0;
		$table['simple']['border_details']['B']['w'] = 0;
		$table['simple']['border_details']['R']['style'] = '';
		$table['simple']['border_details']['L']['style'] = '';
		$table['simple']['border_details']['T']['style'] = '';
		$table['simple']['border_details']['B']['style'] = '';
	}
	else if (!$this->simpleTables) {
	$c['border'] = false;
	$c['border_details']['R']['w'] = 0;
	$c['border_details']['L']['w'] = 0;
	$c['border_details']['T']['w'] = 0;
	$c['border_details']['B']['w'] = 0;
	$c['border_details']['mbw']['BL'] = 0;
	$c['border_details']['mbw']['BR'] = 0;
	$c['border_details']['mbw']['RT'] = 0;
	$c['border_details']['mbw']['RB'] = 0;
	$c['border_details']['mbw']['TL'] = 0;
	$c['border_details']['mbw']['TR'] = 0;
	$c['border_details']['mbw']['LT'] = 0;
	$c['border_details']['mbw']['LB'] = 0;
	$c['border_details']['R']['style'] = '';
	$c['border_details']['L']['style'] = '';
	$c['border_details']['T']['style'] = '';
	$c['border_details']['B']['style'] = '';
	$c['border_details']['R']['s'] = 0;
	$c['border_details']['L']['s'] = 0;
	$c['border_details']['T']['s'] = 0;
	$c['border_details']['B']['s'] = 0;
	$c['border_details']['R']['c'] = $this->ConvertColor(0);
	$c['border_details']['L']['c'] = $this->ConvertColor(0);
	$c['border_details']['T']['c'] = $this->ConvertColor(0);
	$c['border_details']['B']['c'] = $this->ConvertColor(0);
	$c['border_details']['R']['dom'] = 0;
	$c['border_details']['L']['dom'] = 0;
	$c['border_details']['T']['dom'] = 0;
	$c['border_details']['B']['dom'] = 0;
	}


	if ($table['va']) { $c['va'] = $table['va']; }
	if ($table['txta']) { $c['a'] = $table['txta']; }
	if ($this->table_border_attr_set) {
	  if ($table['border_details']) {
	    if (!$this->simpleTables){
		$c['border_details']['R'] = $table['border_details']['R'];
		$c['border_details']['L'] = $table['border_details']['L'];
		$c['border_details']['T'] = $table['border_details']['T'];
		$c['border_details']['B'] = $table['border_details']['B'];
		$c['border'] = $table['border']; 
		$c['border_details']['L']['dom'] = 1; 
		$c['border_details']['R']['dom'] = 1; 
		$c['border_details']['T']['dom'] = 1; 
		$c['border_details']['B']['dom'] = 1; 
	    }
	    else if ($this->simpleTables && $this->row==0 && $this->col==0){
		$table['simple']['border_details']['R'] = $table['border_details']['R'];
		$table['simple']['border_details']['L'] = $table['border_details']['L'];
		$table['simple']['border_details']['T'] = $table['border_details']['T'];
		$table['simple']['border_details']['B'] = $table['border_details']['B'];
		$table['simple']['border'] = $table['border']; 
	    }
	  }
	} 
	// INHERITED THEAD CSS Properties
	if ($this->tablethead) { 
		if ($this->thead_valign_default) $c['va'] = $align[strtolower($this->thead_valign_default)]; 
		if ($this->thead_textalign_default) $c['a'] = $align[strtolower($this->thead_textalign_default)]; 
		if ($this->thead_font_weight == 'B') { $this->SetStyle('B',true); }
		if ($this->thead_font_style == 'I') { $this->SetStyle('I',true); }
		if ($this->thead_font_smCaps == 'S') { $this->SetStyle('S',true); }
	}

	// INHERITED TFOOT CSS Properties
	if ($this->tabletfoot) { 
		if ($this->tfoot_valign_default) $c['va'] = $align[strtolower($this->tfoot_valign_default)]; 
		if ($this->tfoot_textalign_default) $c['a'] = $align[strtolower($this->tfoot_textalign_default)]; 
		if ($this->tfoot_font_weight == 'B') { $this->SetStyle('B',true); }
		if ($this->tfoot_font_style == 'I') { $this->SetStyle('I',true); }
		if ($this->tfoot_font_style == 'S') { $this->SetStyle('S',true); }
	}


	if ($this->trow_text_rotate){
		$c['R'] = $this->trow_text_rotate; 
	}

	$this->cell_border_dominance_L = 0; 
	$this->cell_border_dominance_R = 0; 
	$this->cell_border_dominance_T = 0; 
	$this->cell_border_dominance_B = 0; 

	$properties = $this->cssmgr->MergeCSS('TABLE',$tag,$attr);
	$properties = $this->cssmgr->array_merge_recursive_unique($this->base_table_properties, $properties);

	if (isset($properties['FONT-KERNING']) && (strtoupper($properties['FONT-KERNING'])=='NORMAL' || strtoupper($properties['FONT-KERNING'])=='AUTO')) {
		$this->kerning = true;
	}
	else { $this->kerning = false; }

	if (isset($properties['LETTER-SPACING']) && ($properties['LETTER-SPACING'] || $properties['LETTER-SPACING']==='0') && strtoupper($properties['LETTER-SPACING']) != 'NORMAL') {
		$this->lSpacingCSS = strtoupper($properties['LETTER-SPACING']);
		$this->fixedlSpacing = $this->ConvertSize($this->lSpacingCSS,$this->FontSize); 
	}
	else { 
		$this->lSpacingCSS = '';
		$this->fixedlSpacing = false; 
	}
	if (isset($properties['WORD-SPACING']) && strtoupper($properties['WORD-SPACING']) != 'NORMAL') { 
		$this->wSpacingCSS = strtoupper($properties['WORD-SPACING']);
		$this->minwSpacing = $this->ConvertSize($this->wSpacingCSS,$this->FontSize); 
	}
	else { 
		$this->minwSpacing = 0; 
		$this->wSpacingCSS = '';
	}
	// mPDF 5.6.08
	if (isset($properties['HYPHENS']) && $properties['HYPHENS']) {
		if (strtoupper($properties['HYPHENS']) == 'NONE') { $this->textparam['hyphens'] = 2; }
		else if (strtoupper($properties['HYPHENS']) == 'AUTO') { $this->textparam['hyphens'] = 1; }
		else if (strtoupper($properties['HYPHENS']) == 'MANUAL') { $this->textparam['hyphens'] = 0; }
	}

	if (isset($properties['BACKGROUND-COLOR'])) { $c['bgcolor'] = $properties['BACKGROUND-COLOR']; }
	else if (isset($properties['BACKGROUND'])) { $c['bgcolor'] = $properties['BACKGROUND']; }
	else if (isset($attr['BGCOLOR'])) $c['bgcolor'] = $attr['BGCOLOR'];



/*-- BACKGROUNDS --*/
	if (isset($properties['BACKGROUND-GRADIENT'])) { $c['gradient'] = $properties['BACKGROUND-GRADIENT']; }
	else { $c['gradient'] = false; }

	if (isset($properties['BACKGROUND-IMAGE']) && $properties['BACKGROUND-IMAGE'] && !$this->keep_block_together) {
		$ret = $this->SetBackground($properties, $this->blk[$this->blklvl]['inner_width']);
		if ($ret) { $c['background-image'] = $ret; }
	}
/*-- END BACKGROUNDS --*/
	if (isset($properties['VERTICAL-ALIGN'])) { $c['va']=$align[strtolower($properties['VERTICAL-ALIGN'])]; }
	else if (isset($attr['VALIGN'])) $c['va'] = $align[strtolower($attr['VALIGN'])];


	// mPDF 5.6.13
	if (isset($properties['TEXT-ALIGN']) && $properties['TEXT-ALIGN']) {
		if (substr($properties['TEXT-ALIGN'],0,1)=='D') { $c['a'] = $properties['TEXT-ALIGN']; }
		else { $c['a'] = $align[strtolower($properties['TEXT-ALIGN'])]; }
	}
	// mPDF 5.6.13
	if (isset($attr['ALIGN']) && $attr['ALIGN']) {
		if (strtolower($attr['ALIGN']) == 'char') { 
			if (isset($attr['CHAR']) && $attr['CHAR']) {
				$char = html_entity_decode($attr['CHAR']);
				$char = strcode2utf($char);
				$d = array_search($char,$this->decimal_align);
				if ($d !== false) { $c['a'] = $d.'R'; }
			}
			else { $c['a'] = 'DPR'; }
		}
		else { $c['a'] = $align[strtolower($attr['ALIGN'])]; }
	}

	if (!$c['a']) {
		if (isset($table['direction']) && $table['direction'] == 'rtl' ) { $c['a'] = 'R'; }
		else { $c['a'] = 'L'; }
	}


	if (isset($properties['TEXT-ROTATE']) && ($properties['TEXT-ROTATE'] || $properties['TEXT-ROTATE']==="0")){
		$c['R'] = $properties['TEXT-ROTATE']; 
	}
	if (isset($properties['BORDER'])) { 
		$bord = $this->border_details($properties['BORDER']);
		if ($bord['s']) {
		   if (!$this->simpleTables){
			$c['border'] = _BORDER_ALL;
			$c['border_details']['R'] = $bord;
			$c['border_details']['L'] = $bord;
			$c['border_details']['T'] = $bord;
			$c['border_details']['B'] = $bord;
			$c['border_details']['L']['dom'] = $this->cell_border_dominance_L; 
			$c['border_details']['R']['dom'] = $this->cell_border_dominance_R; 
			$c['border_details']['T']['dom'] = $this->cell_border_dominance_T; 
			$c['border_details']['B']['dom'] = $this->cell_border_dominance_B; 
		   }
		   else if ($this->simpleTables && $this->row==0 && $this->col==0){
			$table['simple']['border'] = _BORDER_ALL;
			$table['simple']['border_details']['R'] = $bord;
			$table['simple']['border_details']['L'] = $bord;
			$table['simple']['border_details']['T'] = $bord;
			$table['simple']['border_details']['B'] = $bord;
		   }
		}
	}
	if (!$this->simpleTables){
	   if (isset($properties['BORDER-RIGHT']) && $properties['BORDER-RIGHT']) { 
		$c['border_details']['R'] = $this->border_details($properties['BORDER-RIGHT']);
		$this->setBorder($c['border'], _BORDER_RIGHT, $c['border_details']['R']['s']); 
		$c['border_details']['R']['dom'] = $this->cell_border_dominance_R; 
	   }
	   if (isset($properties['BORDER-LEFT']) && $properties['BORDER-LEFT']) { 
		$c['border_details']['L'] = $this->border_details($properties['BORDER-LEFT']);
		$this->setBorder($c['border'], _BORDER_LEFT, $c['border_details']['L']['s']); 
		$c['border_details']['L']['dom'] = $this->cell_border_dominance_L; 
	   }
	   if (isset($properties['BORDER-BOTTOM']) && $properties['BORDER-BOTTOM']) { 
		$c['border_details']['B'] = $this->border_details($properties['BORDER-BOTTOM']);
		$this->setBorder($c['border'], _BORDER_BOTTOM, $c['border_details']['B']['s']); 
		$c['border_details']['B']['dom'] = $this->cell_border_dominance_B; 
	   }
	   if (isset($properties['BORDER-TOP']) && $properties['BORDER-TOP']) { 
		$c['border_details']['T'] = $this->border_details($properties['BORDER-TOP']);
		$this->setBorder($c['border'], _BORDER_TOP, $c['border_details']['T']['s']); 
		$c['border_details']['T']['dom'] = $this->cell_border_dominance_T; 
	   }
	}
	else if ($this->simpleTables && $this->row==0 && $this->col==0){
	   if (isset($properties['BORDER-LEFT']) && $properties['BORDER-LEFT']) { 
		$bord = $this->border_details($properties['BORDER-LEFT']);
			if ($bord['s']) { $table['simple']['border'] = _BORDER_ALL; }
			else { $table['simple']['border'] = 0; }
			$table['simple']['border_details']['R'] = $bord;
			$table['simple']['border_details']['L'] = $bord;
			$table['simple']['border_details']['T'] = $bord;
			$table['simple']['border_details']['B'] = $bord;
	   }
	}

	if ($this->simpleTables && $this->row==0 && $this->col==0 && !$table['borders_separate'] && $table['simple']['border'] ){
		$table['border_details'] = $table['simple']['border_details'];
		$table['border'] = $table['simple']['border']; 
	}

	// Border set on TR (if collapsed only)
	if (!$table['borders_separate'] && !$this->simpleTables && isset($table['trborder-left'][$this->row])) {
		if ($this->col==0) { 
			$left = $this->border_details($table['trborder-left'][$this->row]);
			$c['border_details']['L'] = $left;
			$this->setBorder($c['border'], _BORDER_LEFT, $c['border_details']['L']['s']); 
		}
		$c['border_details']['B'] = $this->border_details($table['trborder-bottom'][$this->row]);
		$this->setBorder($c['border'], _BORDER_BOTTOM, $c['border_details']['B']['s']); 
		$c['border_details']['T'] = $this->border_details($table['trborder-top'][$this->row]);
		$this->setBorder($c['border'], _BORDER_TOP, $c['border_details']['T']['s']); 
	}

	if ($this->packTableData && !$this->simpleTables) {
		$c['borderbin'] = $this->_packCellBorder($c);
		unset($c['border']);
		unset($c['border_details']);
	}

	if (isset($properties['PADDING-LEFT'])) { 
		$c['padding']['L'] = $this->ConvertSize($properties['PADDING-LEFT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
	}
	if (isset($properties['PADDING-RIGHT'])) { 
		$c['padding']['R'] = $this->ConvertSize($properties['PADDING-RIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
	}
	if (isset($properties['PADDING-BOTTOM'])) { 
		$c['padding']['B'] = $this->ConvertSize($properties['PADDING-BOTTOM'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
	}
	if (isset($properties['PADDING-TOP'])) { 
		$c['padding']['T'] = $this->ConvertSize($properties['PADDING-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
	}

	$w = '';
	if (isset($properties['WIDTH'])) { $w = $properties['WIDTH']; }
	else if (isset($attr['WIDTH'])) { $w = $attr['WIDTH']; }
	if ($w) { 
		if (strpos($w,'%') && !$this->ignore_table_percents ) { $c['wpercent'] = $w + 0; }	// makes 80% -> 80
		else if (!strpos($w,'%') && !$this->ignore_table_widths ) { $c['w'] = $this->ConvertSize($w,$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
	}

	if (isset($properties['HEIGHT']) && !strpos($properties['HEIGHT'],'%')) { $c['h'] = $this->ConvertSize($properties['HEIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
	else if (isset($attr['HEIGHT']) && !strpos($attr['HEIGHT'],'%')) $c['h'] = $this->ConvertSize($attr['HEIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);


	if (isset($properties['COLOR'])) {
	  $cor = $this->ConvertColor($properties['COLOR']);
	  if ($cor) { 
		$this->colorarray = $cor;
		$this->SetTColor($cor);	
	  }
	}
	if (isset($properties['FONT-FAMILY'])) {
		$this->SetFont($properties['FONT-FAMILY'],'',0,false);
	}
	if (isset($properties['FONT-SIZE'])) { 
	   $mmsize = $this->ConvertSize($properties['FONT-SIZE'],$this->default_font_size/_MPDFK);
	   if ($mmsize) {
  		$this->SetFontSize($mmsize*(_MPDFK),false);
	   }
	}
	$c['dfs'] = $this->FontSize;	// Default Font size
	if (isset($properties['FONT-WEIGHT'])) {
		if (strtoupper($properties['FONT-WEIGHT']) == 'BOLD')	{ $this->SetStyle('B',true); }
	}
	if (isset($properties['FONT-STYLE'])) {
		if (strtoupper($properties['FONT-STYLE']) == 'ITALIC') { $this->SetStyle('I',true); }
	}
	if (isset($properties['FONT-VARIANT'])) {
		if (strtoupper($properties['FONT-VARIANT']) == 'SMALL-CAPS') { $this->SetStyle('S',true); }
	}
	if (isset($properties['TEXT-DECORATION'])) {
		if (strtoupper($properties['TEXT-DECORATION']) == 'LINE-THROUGH') { $this->strike = true; }
		else if (strtoupper($properties['TEXT-DECORATION']) == 'UNDERLINE') { $this->SetStyle('U',true); }
	}
	if (isset($properties['TEXT-SHADOW'])) {
		$ts = $this->cssmgr->setCSStextshadow($properties['TEXT-SHADOW']);
		if ($ts) { $this->textshadow = $ts; }
	}
	if (isset($properties['TEXT-TRANSFORM'])) {
		if (strtoupper($properties['TEXT-TRANSFORM']) == 'CAPITALIZE') { $this->capitalize = true; }
		else if (strtoupper($properties['TEXT-TRANSFORM']) == 'UPPERCASE') { $this->toupper = true; }
		else if (strtoupper($properties['TEXT-TRANSFORM']) == 'LOWERCASE') { $this->tolower = true; }
	}
	if (isset($properties['WHITE-SPACE'])) {
		if (strtoupper($properties['WHITE-SPACE']) == 'NOWRAP') { $c['nowrap']= 1; }
	}
	$properties = array();

	if (isset($attr['TEXT-ROTATE'])) {
		$c['R'] = $attr['TEXT-ROTATE']; 
	}
	if (isset($attr['NOWRAP']) && $attr['NOWRAP']) $c['nowrap']= 1;

	$this->cell[$this->row][$this->col] = $c;
	unset($c);
	$this->cell[$this->row][$this->col]['s'] = 0 ;

	$cs = $rs = 1;
	if (isset($attr['COLSPAN']) && $attr['COLSPAN']>1)	$cs = $this->cell[$this->row][$this->col]['colspan']	= $attr['COLSPAN'];
	if ($this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['nc'] < $this->col+$cs) { 
		$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['nc'] = $this->col+$cs; 
	}	// following code moved outside if...
	for($l=$this->col; $l < $this->col+$cs ;$l++) {
		if ($l-$this->col) $this->cell[$this->row][$l] = 0;
	}
	if (isset($attr['ROWSPAN']) && $attr['ROWSPAN']>1)	$rs = $this->cell[$this->row][$this->col]['rowspan']	= $attr['ROWSPAN'];
	for ($k=$this->row ; $k < $this->row+$rs ;$k++) {
		for($l=$this->col; $l < $this->col+$cs ;$l++) {
			if ($k-$this->row || $l-$this->col)	$this->cell[$k][$l] = 0;
		}
	}
	unset($table);
	break;
/*-- END TABLES --*/


/*-- LISTS --*/
    // *********** LISTS ********************
    case 'OL':
    case 'UL':
	$this->listjustfinished = false;

	if ($this->blockjustfinished && !count($this->textbuffer) && $this->y != $this->tMargin && $this->collapseBlockMargins) { $lastbottommargin = $this->lastblockbottommargin; }
	else { $lastbottommargin = 0; }
	$this->lastblockbottommargin = 0;
	$this->blockjustfinished=false;

	$this->linebreakjustfinished=false;
	$this->lastoptionaltag = ''; // Save current HTML specified optional endtag
	$this->cssmgr->listCSSlvl++;
	if((!$this->tableLevel) && ($this->listlvl == 0)) {
		$blockstate = 0; 
		//if ($this->lastblocklevelchange == 1) { $blockstate = -1; }	// Top margins/padding only
		//else if ($this->lastblocklevelchange < 1) { $blockstate = 0; }	// NO margins/padding
		// called from block after new div e.g. <div> ... <ol> ...    Outputs block top margin/border and padding
		if (count($this->textbuffer) == 0 && $this->lastblocklevelchange == 1 && !$this->tableLevel && !$this->kwt) {
			$this->newFlowingBlock( $this->blk[$this->blklvl]['width'],$this->lineheight,'',false,false,1,true, $this->blk[$this->blklvl]['direction']);
			$this->finishFlowingBlock(true);	// true = END of flowing block
		}
		else if (count($this->textbuffer)) { $this->printbuffer($this->textbuffer,$blockstate); }
		$this->textbuffer=array();
		$this->lastblocklevelchange = -1;
	}
	// ol and ul types are mixed here
	if ($this->listlvl == 0) {
		$this->list_indent = array();
		$this->list_align = array();
		$this->list_lineheight = array();
		$this->InlineProperties['LIST'] = array();
		$this->InlineProperties['LISTITEM'] = array();
	}

/*-- TABLES --*/
	// A simple list for inside a table
	if($this->tableLevel) {
		$this->list_indent[$this->listlvl] = 0;	// mm default indent for each level
		if ($tag == 'OL') $this->listtype = '1';
		else if ($tag == 'UL') $this->listtype = 'disc';
      	if ($this->listlvl > 0) {
			$this->listlist[$this->listlvl]['MAXNUM'] = $this->listnum; //save previous lvl's maxnum
		}
		$this->listlvl++;
		// mPDF 5.6.15
		if (isset($attr['START'])) { $this->listnum = intval($attr['START']); }
		else { $this->listnum = 0; }
		$this->listlist[$this->listlvl] = array('TYPE'=>$this->listtype,'MAXNUM'=>$this->listnum);
		break;
	}
/*-- END TABLES --*/


	if (($this->PDFA || $this->PDFX) && $tag == 'UL') {
		if (($this->PDFA && !$this->PDFAauto) || ($this->PDFX && !$this->PDFXauto)) { $this->PDFAXwarnings[] = "List bullets cannot use core font Zapfdingbats in PDFA1-b or PDFX/1-a. (Substitute characters from current font used if available, otherwise substitutes hyphen '-')"; }
	}

	if ($this->cssmgr->listCSSlvl==1) {
		$properties = $this->cssmgr->MergeCSS('TOPLIST',$tag,$attr);
	}
	else {
		$properties = $this->cssmgr->MergeCSS('LIST',$tag,$attr);
	}
	if (!empty($properties)) $this->setCSS($properties,'LIST');	
	// List-type

	$this->listtype = '';
	if (isset($properties['LIST-STYLE-TYPE'])) { 
		$this->listtype = $this->_getListStyle($properties['LIST-STYLE-TYPE']);
	}
	else if (isset($properties['LIST-STYLE'])) { 
		$this->listtype = $this->_getListStyle($properties['LIST-STYLE']);
	}
	else if (isset($attr['TYPE']) && $attr['TYPE']) { $this->listtype = $attr['TYPE']; }
	if (!$this->listtype) {
		if ($tag == 'OL') $this->listtype = '1';
		if ($tag == 'UL') {
			if ($this->listlvl % 3 == 0) $this->listtype = 'disc';
			elseif ($this->listlvl % 3 == 1) $this->listtype = 'circle';
			else $this->listtype = 'square';
		}
	}
      if ($this->listlvl == 0) {
	  $this->inherit_lineheight = 0;
	  $this->listlvl++; // first depth level
	  // mPDF 5.6.15
	  if (isset($attr['START'])) { $this->listnum = intval($attr['START']); }
	  else { $this->listnum = 0; }
	  $this->listDir = (isset($this->blk[$this->blklvl]['direction']) ? $this->blk[$this->blklvl]['direction'] : null);
	  $occur = $this->listoccur[$this->listlvl] = 1;
	  $this->listlist[$this->listlvl][1] = array('TYPE'=>$this->listtype,'MAXNUM'=>$this->listnum);
      }
      else {
	if (!empty($this->textbuffer))
        {
		$this->listitem[] = array($this->listlvl,$this->listnum,$this->textbuffer,$this->listoccur[$this->listlvl],$this->listitemtype);
		$this->listnum++;
        }
	  // Save current lineheight to inherit
	  $this->textbuffer = array();
  	  $occur = $this->listoccur[$this->listlvl];
	  $this->listlist[$this->listlvl][$occur]['MAXNUM'] = $this->listnum; //save previous lvl's maxnum
	  $this->listlvl++;
	  // mPDF 5.6.15
	  if (isset($attr['START'])) { $this->listnum = intval($attr['START']); }
	  else { $this->listnum = 0; }


	  if (!isset($this->listoccur[$this->listlvl]) || $this->listoccur[$this->listlvl] == 0) $this->listoccur[$this->listlvl] = 1;
	  else $this->listoccur[$this->listlvl]++;
  	  $occur = $this->listoccur[$this->listlvl];
	  $this->listlist[$this->listlvl][$occur] = array('TYPE'=>$this->listtype,'MAXNUM'=>$this->listnum);
      }


	// TOP LEVEL ONLY
	if ($this->listlvl == 1) {
	   if (isset($properties['MARGIN-TOP'])) { 
		if ($lastbottommargin) { 
			$tmp = $this->ConvertSize($properties['MARGIN-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
			if ($tmp > $lastbottommargin) { $properties['MARGIN-TOP'] -= $lastbottommargin; }
			else { $properties['MARGIN-TOP'] = 0; }
		}
		$this->DivLn($this->ConvertSize($properties['MARGIN-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false),$this->blklvl,true,1); 	// collapsible
	   }
	   if (isset($properties['MARGIN-BOTTOM'])) { 
		$this->list_margin_bottom = $this->ConvertSize($properties['MARGIN-BOTTOM'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); 
	   }
	   
	   if (isset($this->blk[$this->blklvl]['line_height'])) {
		$this->list_lineheight[$this->listlvl][$occur] = $this->blk[$this->blklvl]['line_height'];
	   }

	   if (isset($properties['DIRECTION']) && $properties['DIRECTION']) { $this->listDir = strtolower($properties['DIRECTION']); }
	   else if (isset($attr['DIR']) && $attr['DIR']) { $this->listDir = strtolower($attr['DIR']); }

	}
	$this->list_indent[$this->listlvl][$occur] = 5;	// mm default indent for each level
	if (isset($properties['TEXT-INDENT'])) { $this->list_indent[$this->listlvl][$occur] = $this->ConvertSize($properties['TEXT-INDENT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }

	if (isset($properties['TEXT-ALIGN'])) { 
		$this->list_align[$this->listlvl][$occur] = $align[strtolower($properties['TEXT-ALIGN'])]; 
	}


	if (isset($properties['LINE-HEIGHT'])) { 
		$this->list_lineheight[$this->listlvl][$occur] = $this->fixLineheight($properties['LINE-HEIGHT']);
	}
	else if ($this->listlvl>1 && isset($this->list_lineheight[($this->listlvl - 1)][1])) { 
		$this->list_lineheight[$this->listlvl][$occur] = end($this->list_lineheight[($this->listlvl - 1)]);
	}
	if (!isset($this->list_lineheight[$this->listlvl][$occur]) || !$this->list_lineheight[$this->listlvl][$occur]) { 
		$this->list_lineheight[$this->listlvl][$occur] = $this->normalLineheight; 
	}

	$this->InlineProperties['LIST'][$this->listlvl][$occur] = $this->saveInlineProperties();
	$properties = array();
     break;



    case 'LI':
	// Start Block
	$this->lastoptionaltag = $tag; // Save current HTML specified optional endtag
      $this->ignorefollowingspaces = true; //Eliminate exceeding left-side spaces
/*-- TABLES --*/
	// A simple list for inside a table
	if($this->tableLevel) {
	   $this->blockjustfinished=false;

	   // If already something in the Cell
	   if ((isset($this->cell[$this->row][$this->col]['maxs']) && $this->cell[$this->row][$this->col]['maxs'] > 0 ) || $this->cell[$this->row][$this->col]['s'] > 0 ) {
			$this->_saveCellTextBuffer("\n");
			if (!isset($this->cell[$this->row][$this->col]['maxs'])) {
				$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s'];
			}
			elseif($this->cell[$this->row][$this->col]['maxs'] < $this->cell[$this->row][$this->col]['s']) {
				$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s']; 
			}
			$this->cell[$this->row][$this->col]['s'] = 0 ;
		}
		if ($this->listlvl == 0) { //in case of malformed HTML code. Example:(...)</p><li>Content</li><p>Paragraph1</p>(...)
			$this->listlvl++; // first depth level
			$this->listnum = 0; // reset
			$this->listlist[$this->listlvl] = array('TYPE'=>'disc','MAXNUM'=>$this->listnum);
		}

		$this->listnum++;
		switch($this->listlist[$this->listlvl]['TYPE']) {
		case 'A':
			$blt = $this->dec2alpha($this->listnum,true).$this->list_number_suffix;
			break;
		case 'a':
			$blt = $this->dec2alpha($this->listnum,false).$this->list_number_suffix;
			break;
		case 'I':
			$blt = $this->dec2roman($this->listnum,true).$this->list_number_suffix;
			break;
		case 'i':
			$blt = $this->dec2roman($this->listnum,false).$this->list_number_suffix;
			break;
		case '1':
			$blt = $this->listnum.$this->list_number_suffix;
            	break;
		default:
			if ($this->listlvl % 3 == 1 && $this->_charDefined($this->CurrentFont['cw'],8226)) { $blt = "\xe2\x80\xa2"; } 	// &#8226; 
			else if ($this->listlvl % 3 == 2 && $this->_charDefined($this->CurrentFont['cw'],9900)) { $blt = "\xe2\x9a\xac"; } // &#9900; 
			else if ($this->listlvl % 3 == 0 && $this->_charDefined($this->CurrentFont['cw'],9642)) { $blt = "\xe2\x96\xaa"; } // &#9642; 
			else { $blt = '-'; }
			break;
		}

		// change to &nbsp; spaces
		if ($this->usingCoreFont) { 
			$ls = str_repeat(chr(160).chr(160),($this->listlvl-1)*2) . $blt . ' '; 
		}
		else {
			$ls = str_repeat("\xc2\xa0\xc2\xa0",($this->listlvl-1)*2) . $blt . ' '; 
		}

		$this->_saveCellTextBuffer($ls, $this->HREF);
		$this->cell[$this->row][$this->col]['s'] += $this->GetStringWidth($ls);
		break;
	}
/*-- END TABLES --*/
	//Observation: </LI> is ignored
	if ($this->listlvl == 0) { //in case of malformed HTML code. Example:(...)</p><li>Content</li><p>Paragraph1</p>(...)
	//First of all, skip a line
		$this->listlvl++; // first depth level
		$this->listnum = 0; // reset
		$this->listoccur[$this->listlvl] = 1;
		$this->listlist[$this->listlvl][1] = array('TYPE'=>'disc','MAXNUM'=>$this->listnum);
	}
	if ($this->listnum == 0) {
		$this->listnum++;
		$this->textbuffer = array();
	}
	else {
		if (!empty($this->textbuffer)) {
		  if (!$this->listjustfinished) {
			$this->listitem[] = array($this->listlvl,$this->listnum,$this->textbuffer,$this->listoccur[$this->listlvl],$this->listitemtype);
			$this->listnum++;
		  }
		  else {
			$this->listitem[] = array($this->listlvl,$this->listnum,$this->textbuffer,$this->listoccur[$this->listlvl],$this->listitemtype, true);
		  }
		}
		$this->textbuffer = array();
      }
	$this->listjustfinished = false;

	$this->cssmgr->listCSSlvl++;
	$properties = $this->cssmgr->MergeCSS('LIST',$tag,$attr);
	if (!empty($properties)) $this->setCSS($properties,'LIST');
	$this->InlineProperties['LISTITEM'][$this->listlvl][$this->listoccur[$this->listlvl]][$this->listnum] = $this->saveInlineProperties();

	// List-type
	if (isset($properties['LIST-STYLE-TYPE'])) { 
		$this->listitemtype = $this->_getListStyle($properties['LIST-STYLE-TYPE']);
	}
	else if (isset($properties['LIST-STYLE'])) { 
		$this->listitemtype = $this->_getListStyle($properties['LIST-STYLE']);
	}
	else if (isset($attr['TYPE']) && $attr['TYPE']) { $this->listitemtype = $attr['TYPE']; }
	else $this->listitemtype = '';
      break;
/*-- END LISTS --*/

  }//end of switch
}

/*-- LISTS --*/

function _getListStyle($ls) {
 	if (stristr($ls,'decimal')) { return '1'; }
/*  CSS3 list-styles numeric (selected) + I added tamil
arabic-indic | bengali | devanagari | gujarati | gurmukhi | kannada | malayalam | oriya | persian | telugu | thai | urdu 
*/
	else if (preg_match('/(disc|circle|square|arabic-indic|bengali|devanagari|gujarati|gurmukhi|kannada|malayalam|oriya|persian|tamil|telugu|thai|urdu)/i',$ls,$m)) { 
		return strtolower(trim($m[1])); 
	}
	else if (stristr($ls,'lower-roman')) { return 'i'; }
	else if (stristr($ls,'upper-roman')) { return 'I'; }
	else if (stristr($ls,'lower-latin')|| stristr($ls,'lower-alpha')) { return 'a'; }
	else if (stristr($ls,'upper-latin') || stristr($ls,'upper-alpha')) { return 'A'; }
	else if (stristr($ls,'none')) { return 'none'; }
	else if (preg_match('/U\+([a-fA-F0-9]+)/i',$ls)) { return $ls; }
	else { return ''; }
}
/*-- END LISTS --*/



function CloseTag($tag)
{
	$this->ignorefollowingspaces = false; //Eliminate exceeding left-side spaces
    //Closing tag
    if($tag=='OPTION') { $this->selectoption['ACTIVE'] = false; 	$this->lastoptionaltag = ''; }

    if($tag=='TTS' or $tag=='TTA' or $tag=='TTZ') {
	if ($this->InlineProperties[$tag]) { $this->restoreInlineProperties($this->InlineProperties[$tag]); }
	unset($this->InlineProperties[$tag]);
	$ltag = strtolower($tag);
	$this->$ltag = false;
    }


    if($tag=='FONT' || $tag=='SPAN' || $tag=='CODE' || $tag=='KBD' || $tag=='SAMP' || $tag=='TT' || $tag=='VAR' 
	|| $tag=='INS' || $tag=='STRONG' || $tag=='CITE' || $tag=='SUB' || $tag=='SUP' || $tag=='S' || $tag=='STRIKE' || $tag=='DEL'
	|| $tag=='Q' || $tag=='EM' || $tag=='B' || $tag=='I' || $tag=='U' | $tag=='SMALL' || $tag=='BIG' || $tag=='ACRONYM'
	|| $tag=='MARK'  || $tag=='TIME'  || $tag=='PROGRESS'  || $tag=='METER' 
	) {	// mPDF 5.5.09

	if ($tag == 'SPAN') {
		if (isset($this->InlineProperties['SPAN'][$this->spanlvl]) && $this->InlineProperties['SPAN'][$this->spanlvl]) { $this->restoreInlineProperties($this->InlineProperties['SPAN'][$this->spanlvl]); }
		unset($this->InlineProperties['SPAN'][$this->spanlvl]);
		if (isset($this->InlineAnnots['SPAN'][$this->spanlvl]) && $this->InlineAnnots['SPAN'][$this->spanlvl]) { $annot = $this->InlineAnnots['SPAN'][$this->spanlvl]; }	// *ANNOTATIONS*
		unset($this->InlineAnnots['SPAN'][$this->spanlvl]);	// *ANNOTATIONS*
		$this->spanlvl--;
	}
	else { 
		if (isset($this->InlineProperties[$tag]) && $this->InlineProperties[$tag]) { $this->restoreInlineProperties($this->InlineProperties[$tag]); }
		unset($this->InlineProperties[$tag]);
		if (isset($this->InlineAnnots[$tag]) && $this->InlineAnnots[$tag]) { $annot = $this->InlineAnnots[$tag]; }	// *ANNOTATIONS*
		unset($this->InlineAnnots[$tag]);	// *ANNOTATIONS*
	}

/*-- ANNOTATIONS --*/
	if (isset($annot)) {
		if($this->tableLevel) {	// *TABLES*
			$this->cell[$this->row][$this->col]['textbuffer'][] = array($annot);	// *TABLES*
		}	// *TABLES*
		else  {	// *TABLES*
			$this->textbuffer[] = array($annot);
		}	// *TABLES*
	}
/*-- END ANNOTATIONS --*/
    }

    if($tag=='METER' || $tag=='PROGRESS') {
	$this->inMeter = false;	// mPDF 5.5.09
    }


    if($tag=='A') {
	$this->HREF=''; 
	if (isset($this->InlineProperties['A'])) { $this->restoreInlineProperties($this->InlineProperties['A']); }
	unset($this->InlineProperties['A']);
    }

    if($tag=='LEGEND') {	// mPDF 5.4.18
	if (count($this->textbuffer) && !$this->tableLevel) { 
		$leg = $this->textbuffer[(count($this->textbuffer)-1)]; 
		unset($this->textbuffer[(count($this->textbuffer)-1)]);
		$this->textbuffer = array_values($this->textbuffer);
		$this->blk[$this->blklvl]['border_legend'] = $leg;
		$this->blk[$this->blklvl]['margin_top'] += ($leg[11]/2)/_MPDFK;
		$this->blk[$this->blklvl]['padding_top'] += ($leg[11]/2)/_MPDFK;
	}
	if (isset($this->InlineProperties['LEGEND'])) { $this->restoreInlineProperties($this->InlineProperties['LEGEND']); }
	unset($this->InlineProperties['LEGEND']);
	$this->ignorefollowingspaces = true; //Eliminate exceeding left-side spaces
    }



/*-- FORMS --*/
	// *********** FORM ELEMENTS ********************

    if($tag=='TEXTAREA') {
	$this->specialcontent = '';
	if ($this->InlineProperties[$tag]) { $this->restoreInlineProperties($this->InlineProperties[$tag]); }
	unset($this->InlineProperties[$tag]);
    }


    if($tag=='SELECT') {
	$this->lastoptionaltag = '';
	$texto = '';
	if (isset($this->selectoption['SELECTED'])) { $texto = $this->selectoption['SELECTED']; }

	if ($this->useActiveForms) { $w = $this->selectoption['MAXWIDTH']; }
	else { $w = $this->GetStringWidth($texto); }
	if ($w == 0) { $w = 5; }
	$objattr['type'] = 'select';
	$objattr['text'] = $texto;
	if (isset($this->selectoption['NAME'])) { $objattr['fieldname'] = $this->selectoption['NAME']; }
	if (isset($this->selectoption['READONLY'])) { $objattr['readonly'] = true; }
	if (isset($this->selectoption['REQUIRED'])) { $objattr['required'] = true; }
	if (isset($this->selectoption['SPELLCHECK'])) { $objattr['spellcheck'] = true; }
	if (isset($this->selectoption['EDITABLE'])) { $objattr['editable'] = true; }
	if (isset($this->selectoption['ONCHANGE'])) { $objattr['onChange'] = $this->selectoption['ONCHANGE']; }
	if (isset($this->selectoption['ITEMS'])) { $objattr['items'] = $this->selectoption['ITEMS']; }
	if (isset($this->selectoption['MULTIPLE'])) { $objattr['multiple'] = $this->selectoption['MULTIPLE']; }
	if (isset($this->selectoption['DISABLED'])) { $objattr['disabled'] = $this->selectoption['DISABLED']; }
	if (isset($this->selectoption['TITLE'])) { $objattr['title'] = $this->selectoption['TITLE']; }
	if (isset($this->selectoption['COLOR'])) { $objattr['color'] = $this->selectoption['COLOR']; }
	if (isset($this->selectoption['SIZE'])) { $objattr['size'] = $this->selectoption['SIZE']; }
	if (isset($objattr['size']) && $objattr['size']>1) { $rows=$objattr['size']; } else { $rows = 1; }

	$objattr['fontfamily'] = $this->FontFamily;
	$objattr['fontsize'] = $this->FontSizePt;

	$objattr['width'] = $w + ($this->form->form_element_spacing['select']['outer']['h']*2)+($this->form->form_element_spacing['select']['inner']['h']*2) + ($this->FontSize*1.4);
	$objattr['height'] = ($this->FontSize*$rows) + ($this->form->form_element_spacing['select']['outer']['v']*2)+($this->form->form_element_spacing['select']['inner']['v']*2);
	$e = "\xbb\xa4\xactype=select,objattr=".serialize($objattr)."\xbb\xa4\xac";

	// Clear properties - tidy up
	$properties = array();

	// Output it to buffers
	if ($this->tableLevel) {	// *TABLES*
		$this->_saveCellTextBuffer($e, $this->HREF);
		$this->cell[$this->row][$this->col]['s'] += $objattr['width'] ;	// *TABLES*
	}	// *TABLES*
	else {	// *TABLES*
		$this->_saveTextBuffer($e, $this->HREF);
	}	// *TABLES*

	$this->selectoption = array();
	$this->specialcontent = '';

	if ($this->InlineProperties[$tag]) { $this->restoreInlineProperties($this->InlineProperties[$tag]); }
	unset($this->InlineProperties[$tag]);

    }
/*-- END FORMS --*/


	// *********** BLOCKS ********************
	// mPDF 5.4.18
    if($tag=='P' || $tag=='DIV' || $tag=='H1' || $tag=='H2' || $tag=='H3' || $tag=='H4' || $tag=='H5' || $tag=='H6' || $tag=='PRE' 
	 || $tag=='FORM' || $tag=='ADDRESS' || $tag=='BLOCKQUOTE' || $tag=='CENTER' || $tag=='DT'  || $tag=='DD'  || $tag=='DL'  
	|| $tag=='CAPTION' || $tag=='FIELDSET' 
	|| $tag=='ARTICLE' || $tag=='ASIDE' || $tag=='FIGURE' || $tag=='FIGCAPTION' || $tag=='FOOTER' || $tag=='HEADER' || $tag=='HGROUP' 
	|| $tag=='NAV' || $tag=='SECTION'  || $tag=='DETAILS' || $tag=='SUMMARY'
	) { 	// mPDF 5.5.09	// mPDF 5.5.22

	$this->ignorefollowingspaces = true; //Eliminate exceeding left-side spaces
	$this->blockjustfinished=true;

	$this->lastblockbottommargin = $this->blk[$this->blklvl]['margin_bottom'];
/*-- LISTS --*/
	if ($this->listlvl>0) { return; }
/*-- END LISTS --*/

	// mPDF 5.6.34
	if (preg_match('/^H\d/',$tag) && !$this->tableLevel && !$this->writingToC) {	// mPDF 5.6.38
		if (isset($this->h2toc[$tag]) || isset($this->h2bookmarks[$tag])) {
			$content = '';
			if (count($this->textbuffer)==1) { $content = $this->textbuffer[0][0]; }
			else {
				for ($i=0;$i<count($this->textbuffer);$i++) {
      				if (substr($this->textbuffer[$i][0],0,3) != "\xbb\xa4\xac") { //inline object
						$content .= $this->textbuffer[$i][0];
					}
				}
			}
/*-- TOC --*/
			if (isset($this->h2toc[$tag])) {
				$objattr = array();
				$objattr['type'] = 'toc';
				$objattr['toclevel'] = $this->h2toc[$tag];
				$objattr['CONTENT'] = htmlspecialchars($content);	// mPDF 5.6.37
				$e = "\xbb\xa4\xactype=toc,objattr=".serialize($objattr)."\xbb\xa4\xac";
				array_unshift($this->textbuffer,array($e));
			}
/*-- END TOC --*/
/*-- BOOKMARKS --*/
			if (isset($this->h2bookmarks[$tag])) {
				$objattr = array();
				$objattr['type'] = 'bookmark';
				$objattr['bklevel'] = $this->h2bookmarks[$tag];
				$objattr['CONTENT'] = $content;
				$e = "\xbb\xa4\xactype=toc,objattr=".serialize($objattr)."\xbb\xa4\xac";
				array_unshift($this->textbuffer,array($e));
			}
/*-- END BOOKMARKS --*/
		}
	}

/*-- TABLES --*/
	if($this->tableLevel) {
		if ($this->linebreakjustfinished) { $this->blockjustfinished=false; }
		if (isset($this->InlineProperties['BLOCKINTABLE'])) { 
			if ($this->InlineProperties['BLOCKINTABLE']) { $this->restoreInlineProperties($this->InlineProperties['BLOCKINTABLE']); }
			unset($this->InlineProperties['BLOCKINTABLE']);
		}
		if($tag=='PRE') { $this->ispre=false; }
		return;
	}
/*-- END TABLES --*/
	$this->lastoptionaltag = '';
	$this->divbegin=false;

	$this->linebreakjustfinished=false;

	$this->x = $this->lMargin + $this->blk[$this->blklvl]['outer_left_margin'];

/*-- CSS-FLOAT --*/
	// If float contained in a float, need to extend bottom to allow for it
	$currpos = $this->page*1000 + $this->y;
	if (isset($this->blk[$this->blklvl]['float_endpos']) && $this->blk[$this->blklvl]['float_endpos'] > $currpos) {
		$old_page = $this->page;
		$new_page = intval($this->blk[$this->blklvl]['float_endpos'] /1000);
		if ($old_page != $new_page) {
			$s = $this->PrintPageBackgrounds();
			// Writes after the marker so not overwritten later by page background etc.
			$this->pages[$this->page] = preg_replace('/(___BACKGROUND___PATTERNS'.date('jY').')/', '\\1'."\n".$s."\n", $this->pages[$this->page]);
			$this->pageBackgrounds = array();
			$this->page = $new_page;
			$this->ResetMargins();
			$this->Reset();
			$this->pageoutput[$this->page] = array();
		}
		$this->y = (($this->blk[$this->blklvl]['float_endpos'] *1000) % 1000000)/1000;	// mod changes operands to integers before processing
	}
/*-- END CSS-FLOAT --*/


	//Print content
	if ($this->lastblocklevelchange == 1) { $blockstate = 3; }	// Top & bottom margins/padding
	else if ($this->lastblocklevelchange == -1) { $blockstate = 2; }	// Bottom margins/padding only
	else { $blockstate = 0; }
	// called from after e.g. </table> </div> </div> ...    Outputs block margin/border and padding
	if (count($this->textbuffer) && $this->textbuffer[count($this->textbuffer)-1]) {
	  if (substr($this->textbuffer[count($this->textbuffer)-1][0],0,3) != "\xbb\xa4\xac") {	// not special content
	   if ($this->usingCoreFont) {
		$this->textbuffer[count($this->textbuffer)-1][0] = preg_replace('/[ ]+$/', '', $this->textbuffer[count($this->textbuffer)-1][0]);
	   }
	   else {
		$this->textbuffer[count($this->textbuffer)-1][0] = preg_replace('/[ ]+$/u', '', $this->textbuffer[count($this->textbuffer)-1][0]);	   }
	  }
	}

	if (count($this->textbuffer) == 0 && $this->lastblocklevelchange != 0) {
		//$this->newFlowingBlock( $this->blk[$this->blklvl]['width'],$this->lineheight,'',false,false,2,true, $this->blk[$this->blklvl]['direction']);
		$this->newFlowingBlock( $this->blk[$this->blklvl]['width'],$this->lineheight,'',false,false,$blockstate,true, $this->blk[$this->blklvl]['direction']);
		$this->finishFlowingBlock(true);	// true = END of flowing block
		$this->PaintDivBB('',$blockstate);
	}
	else {
		$this->printbuffer($this->textbuffer,$blockstate); 
	}


	$this->textbuffer=array();

	if ($this->blk[$this->blklvl]['keep_block_together']) {
		$this->printdivbuffer(); 
	}

	if ($this->kwt) {
		$this->kwt_height = $this->y - $this->kwt_y0;
	}

/*-- CSS-IMAGE-FLOAT --*/
	$this->printfloatbuffer();
/*-- END CSS-IMAGE-FLOAT --*/

	if($tag=='PRE') { $this->ispre=false; }

/*-- CSS-FLOAT --*/
	if ($this->blk[$this->blklvl]['float'] == 'R') {
		// If width not set, here would need to adjust and output buffer
		$s = $this->PrintPageBackgrounds();
		// Writes after the marker so not overwritten later by page background etc.
		$this->pages[$this->page] = preg_replace('/(___BACKGROUND___PATTERNS'.date('jY').')/', '\\1'."\n".$s."\n", $this->pages[$this->page]);
		$this->pageBackgrounds = array();
		$this->Reset();
		$this->pageoutput[$this->page] = array();

		for($i=($this->blklvl-1); $i >= 0; $i--) {
			if (isset($this->blk[$i]['float_endpos'])) { $this->blk[$i]['float_endpos'] = max($this->blk[$i]['float_endpos'], ($this->page*1000 + $this->y)); }
			else { $this->blk[$i]['float_endpos'] = $this->page*1000 + $this->y; }
		}

		$this->floatDivs[] = array(
			'side'=>'R',
			'startpage'=>$this->blk[$this->blklvl]['startpage'] ,
			'y0'=>$this->blk[$this->blklvl]['float_start_y'] ,
			'startpos'=> ($this->blk[$this->blklvl]['startpage']*1000 + $this->blk[$this->blklvl]['float_start_y']),
			'endpage'=>$this->page ,
			'y1'=>$this->y ,
			'endpos'=> ($this->page*1000 + $this->y),
			'w'=> $this->blk[$this->blklvl]['float_width'],
			'blklvl'=>$this->blklvl,
			'blockContext' => $this->blk[$this->blklvl-1]['blockContext']
		);

		$this->y = $this->blk[$this->blklvl]['float_start_y'] ;
		$this->page = $this->blk[$this->blklvl]['startpage'] ;
		$this->ResetMargins();
		$this->pageoutput[$this->page] = array();
	}
	if ($this->blk[$this->blklvl]['float'] == 'L') {
		// If width not set, here would need to adjust and output buffer
		$s = $this->PrintPageBackgrounds();
		// Writes after the marker so not overwritten later by page background etc.
		$this->pages[$this->page] = preg_replace('/(___BACKGROUND___PATTERNS'.date('jY').')/', '\\1'."\n".$s."\n", $this->pages[$this->page]);
		$this->pageBackgrounds = array();
		$this->Reset();
		$this->pageoutput[$this->page] = array();

		for($i=($this->blklvl-1); $i >= 0; $i--) {
			if (isset($this->blk[$i]['float_endpos'])) { $this->blk[$i]['float_endpos'] = max($this->blk[$i]['float_endpos'], ($this->page*1000 + $this->y)); }
			else { $this->blk[$i]['float_endpos'] = $this->page*1000 + $this->y; }
		}

		$this->floatDivs[] = array(
			'side'=>'L',
			'startpage'=>$this->blk[$this->blklvl]['startpage'] ,
			'y0'=>$this->blk[$this->blklvl]['float_start_y'] ,
			'startpos'=> ($this->blk[$this->blklvl]['startpage']*1000 + $this->blk[$this->blklvl]['float_start_y']),
			'endpage'=>$this->page ,
			'y1'=>$this->y ,
			'endpos'=> ($this->page*1000 + $this->y),
			'w'=> $this->blk[$this->blklvl]['float_width'],
			'blklvl'=>$this->blklvl,
			'blockContext' => $this->blk[$this->blklvl-1]['blockContext']
		);

		$this->y = $this->blk[$this->blklvl]['float_start_y'] ;
		$this->page = $this->blk[$this->blklvl]['startpage'] ;
		$this->ResetMargins();
		$this->pageoutput[$this->page] = array();
	}
/*-- END CSS-FLOAT --*/

	if (isset($this->blk[$this->blklvl]['visibility']) && $this->blk[$this->blklvl]['visibility']!='visible') {
		$this->SetVisibility('visible');
	}

	if (isset($this->blk[$this->blklvl]['page_break_after'])) { $page_break_after = $this->blk[$this->blklvl]['page_break_after']; }
	else { $page_break_after = ''; }

	//Reset values
	$this->Reset();

	// mPDF 5.6.01  - LAYERS
	if (isset($this->blk[$this->blklvl]['z-index']) && $this->blk[$this->blklvl]['z-index'] > 0) {
		$this->EndLayer();
	}

	if ($this->blklvl > 0) {	// ==0 SHOULDN'T HAPPEN - NOT XHTML 
	   if ($this->blk[$this->blklvl]['tag'] == $tag) {
		unset($this->blk[$this->blklvl]);
		$this->blklvl--;
	   }
	   //else { echo $tag; exit; }	// debug - forces error if incorrectly nested html tags
	}

	$this->lastblocklevelchange = -1 ;
	// Reset Inline-type properties
	if (isset($this->blk[$this->blklvl]['InlineProperties'])) { $this->restoreInlineProperties($this->blk[$this->blklvl]['InlineProperties']); }

	$this->x = $this->lMargin + $this->blk[$this->blklvl]['outer_left_margin'];

	if ($page_break_after) {
		$save_blklvl = $this->blklvl;
		$save_blk = $this->blk;
		$save_silp = $this->saveInlineProperties();
		$save_spanlvl = $this->spanlvl;
		$save_ilp = $this->InlineProperties;
		if ($this->blklvl>1) {
			// Close any open block tags
			for ($b= $this->blklvl;$b>0;$b--) { $this->CloseTag($this->blk[$b]['tag']); }
			// Output any text left in buffer
			if (count($this->textbuffer)) { $this->printbuffer($this->textbuffer); $this->textbuffer=array(); }
		}
/*-- COLUMNS --*/
		$save_cols = false;
		if ($this->ColActive) {
			$save_cols = true;
			$save_nbcol = $this->NbCol;	// other values of gap and vAlign will not change by setting Columns off
			$this->SetColumns(0);
		}
/*-- END COLUMNS --*/
		if ($page_break_after == 'RIGHT') { $this->AddPage($this->CurOrientation,'NEXT-ODD','','','','','', '','', '','','','','','',0,0,0,0,$pagesel); }
		else if ($page_break_after == 'LEFT') { $this->AddPage($this->CurOrientation,'NEXT-EVEN','','','','','', '','', '','','','','','',0,0,0,0,$pagesel); }
		else { $this->AddPage($this->CurOrientation,'','','','','','', '','', '','','','','','',0,0,0,0,$pagesel); }
		if (!$this->restoreBlockPagebreaks) {
			$this->blklvl = 0;
			$this->lastblocklevelchange = 0;
			$this->blk = array();
			$this->initialiseBlock($this->blk[0]);
			$this->blk[0]['width'] =& $this->pgwidth;
			$this->blk[0]['inner_width'] =& $this->pgwidth;
			$this->blk[0]['blockContext'] = $this->blockContext;
			$properties = $this->cssmgr->MergeCSS('BLOCK','BODY','');
			$this->setCSS($properties,'','BODY'); 
			$this->blklvl++;
			$currblk =& $this->blk[$this->blklvl];
			$prevblk =& $this->blk[$this->blklvl-1];

			$this->initialiseBlock($currblk);
			$currblk['tag'] = $tag;
			$currblk['attr'] = $attr;

			$this->Reset();
			$properties = $this->cssmgr->MergeCSS('BLOCK',$tag,$attr);
		}
/*-- COLUMNS --*/
		if ($save_cols) {
			// Restore columns
			$this->SetColumns($save_nbcol,$this->colvAlign,$this->ColGap);
		}
/*-- END COLUMNS --*/
		if ($this->restoreBlockPagebreaks && !$this->tableLevel && !$this->listlvl) {
			$this->blk = $save_blk;
			// Re-open block tags
			$t = $this->blk[0]['tag'];
			$a = $this->blk[0]['attr'];
			$this->blklvl = 0; 
			for ($b=0; $b<=$save_blklvl;$b++) {
				$tc = $t;
				$ac = $a;
				$t = $this->blk[$b+1]['tag'];
				$a = $this->blk[$b+1]['attr'];
				unset($this->blk[$b+1]);
				$this->OpenTag($tc,$ac); 
			}
			$this->spanlvl = $save_spanlvl;
			$this->InlineProperties = $save_ilp;
			$this->restoreInlineProperties($save_silp);
		}
	}

    }


/*-- TABLES --*/

    if($tag=='TH') $this->SetStyle('B',false);

    if(($tag=='TH' or $tag=='TD') && $this->tableLevel) {
	$this->lastoptionaltag = 'TR';
	unset($this->cssmgr->tablecascadeCSS[$this->cssmgr->tbCSSlvl]);
	$this->cssmgr->tbCSSlvl--;
	if (!$this->tdbegin) { return; }
	$this->tdbegin = false;
	// Added for correct calculation of cell column width - otherwise misses the last line if not end </p> etc.
	if (!isset($this->cell[$this->row][$this->col]['maxs'])) {
		if (!is_array($this->cell[$this->row][$this->col])) { $this->Error("You may have an error in your HTML code e.g. &lt;/td&gt;&lt;/td&gt;"); }
		$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s']; 
	}
	elseif($this->cell[$this->row][$this->col]['maxs'] < $this->cell[$this->row][$this->col]['s']) {
		$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s']; 
	}

	// Remove last <br> if at end of cell
	if (isset($this->cell[$this->row][$this->col]['textbuffer'])) { $ntb = count($this->cell[$this->row][$this->col]['textbuffer']); }
	else { $ntb = 0; }
	if ($ntb>1 && $this->cell[$this->row][$this->col]['textbuffer'][$ntb-1][0] == "\n") {
		unset($this->cell[$this->row][$this->col]['textbuffer'][$ntb-1]);
	}

	if ($this->cacheTables) {
		$clen = $this->_cacheCell($this->cell[$this->row][$this->col], $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['cache']);
		$this->cell[$this->row][$this->col] = $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['ptr'];
		$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['ptr'] += $clen;
	}

	if ($this->tablethead) { 
		$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['is_thead'][$this->row] = true;
		if ($this->tableLevel==1) { $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['headernrows'] = max($this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['headernrows'] , ($this->row+1)); }
	}
	if ($this->tabletfoot) { 
		$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['is_tfoot'][$this->row] = true;
		if ($this->tableLevel==1) { $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['footernrows'] = max($this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['footernrows'] , ($this->row+1 - $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['headernrows'] )); }
	}
	$this->Reset();
    }

    if($tag=='TR' && $this->tableLevel) {
	// If Border set on TR - Update right border
	if (isset($this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['trborder-left'][$this->row])) {
		if ($this->cacheTables) {
			$c = $this->_uncacheCell($this->cell[$this->row][$this->col], $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['cache'], null);
		}
		else { $c =& $this->cell[$this->row][$this->col]; }
		if ($c) {
			if ($this->packTableData) {
				$cell = $this->_unpackCellBorder($c['borderbin'] );
			}
			else { $cell = $c; }
			$cell['border_details']['R'] = $this->border_details($this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['trborder-right'][$this->row]);
			$this->setBorder($cell['border'], _BORDER_RIGHT, $cell['border_details']['R']['s']); 
			if ($this->packTableData) {
				$c['borderbin'] = $this->_packCellBorder($cell);
				unset($c['border']);
				unset($c['border_details']);
			}
			else { $c = $cell; }
		}
		if ($this->cacheTables) {
			$fh = fopen($this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['cache'], "r+b"); 
			$this->_cacheUpdateBorder($c, $fh, $this->cell[$this->row][$this->col]);
			fclose($fh);
		}
	}
	$this->lastoptionaltag = '';
	unset($this->cssmgr->tablecascadeCSS[$this->cssmgr->tbCSSlvl]);
	$this->cssmgr->tbCSSlvl--;
	$this->trow_text_rotate = '';
	$this->tabletheadjustfinished = false;
   }

    if($tag=='TBODY') {
	$this->lastoptionaltag = '';
	unset($this->cssmgr->tablecascadeCSS[$this->cssmgr->tbCSSlvl]);
	$this->cssmgr->tbCSSlvl--;
    }

    if($tag=='THEAD') {
	$this->lastoptionaltag = '';
	unset($this->cssmgr->tablecascadeCSS[$this->cssmgr->tbCSSlvl]);
	$this->cssmgr->tbCSSlvl--;
	$this->tablethead = 0;
	$this->tabletheadjustfinished = true;
	$this->ResetStyles();
	$this->thead_font_weight = '';
	$this->thead_font_style = '';
	$this->thead_font_smCaps = '';

	$this->thead_valign_default = '';
	$this->thead_textalign_default = '';
    }

    if($tag=='TFOOT') {
	$this->lastoptionaltag = '';
	unset($this->cssmgr->tablecascadeCSS[$this->cssmgr->tbCSSlvl]);
	$this->cssmgr->tbCSSlvl--;
	$this->tabletfoot = 0;
	$this->ResetStyles();
	$this->tfoot_font_weight = '';
	$this->tfoot_font_style = '';
	$this->tfoot_font_smCaps = '';

	$this->tfoot_valign_default = '';
	$this->tfoot_textalign_default = '';
    }

    if($tag=='TABLE') { // TABLE-END (
	if ($this->progressBar) { $this->UpdateProgressBar(1,'','TABLE'); }	// *PROGRESS-BAR*
	if ($this->progressBar) { $this->UpdateProgressBar(7,0,''); }	// *PROGRESS-BAR*
	$this->lastoptionaltag = '';
	unset($this->cssmgr->tablecascadeCSS[$this->cssmgr->tbCSSlvl]);
	$this->cssmgr->tbCSSlvl--;
	$this->ignorefollowingspaces = true; //Eliminate exceeding left-side spaces
	$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['cells'] = $this->cell;
	$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['wc'] = array_pad(array(),$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['nc'],array('miw'=>0,'maw'=>0));
	$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['hr'] = array_pad(array(),$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['nr'],0);

	// Move table footer <tfoot> row to end of table
	if (isset($this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['is_tfoot']) && count($this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['is_tfoot'])) {
		$tfrows = array();
		foreach($this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['is_tfoot'] AS $r=>$val) {
			if ($val) { $tfrows[] = $r; }
		}
		$temp = array();
		$temptf = array();
		foreach($this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['cells'] AS $k=>$row) {
			if (in_array($k,$tfrows)) {
				$temptf[] = $row;
			}
			else {
				$temp[] = $row;
			}
		}
		$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['is_tfoot'] = array();
		for($i=count($temp) ; $i<(count($temp)+count($temptf)); $i++) {
			$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['is_tfoot'][$i] = true;
		}
		// Update nestedpos row references
		if (count($this->table[($this->tableLevel+1)])) {
		  foreach($this->table[($this->tableLevel+1)] AS $nid=>$nested) {
			$this->table[($this->tableLevel+1)][$nid]['nestedpos'][0] -= count($temptf);
		  }
		} 
		$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['cells'] = array_merge($temp, $temptf);

		// Update other arays set on row number
		// [trbackground-images] [trgradients]
		$temptrbgi = array();
		$temptrbgg = array();
		$temptrbgc = array();
		$temptrbgc[-1] = $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['bgcolor'][-1];
		for($k=0; $k<$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['nr']; $k++) {
			if (!in_array($k,$tfrows)) {
				$temptrbgi[] = $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['trbackground-images'][$k];
				$temptrbgg[] = $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['trgradients'][$k];
				$temptrbgc[] = $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['bgcolor'][$k];
			}
		}
		for($k=0; $k<$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['nr']; $k++) {
			if (in_array($k,$tfrows)) {
				$temptrbgi[] = $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['trbackground-images'][$k];
				$temptrbgg[] = $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['trgradients'][$k];
				$temptrbgc[] = $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['bgcolor'][$k];
			}
		}
		$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['trbackground-images'] = $temptrbgi;
		$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['trgradients'] = $temptrbgg;
		$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['bgcolor'] = $temptrbgc ;
		// Should Update all other arays set on row number, but cell properties have been set so not needed
		// [bgcolor] [trborder-left] [trborder-right] [trborder-top] [trborder-bottom]
	}

	if ($this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['direction']=='rtl') {
		$this->_reverseTableDir($this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]);
	}

	// Fix Borders *********************************************
	$this->_fixTableBorders($this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]);

	if ($this->progressBar) { $this->UpdateProgressBar(7,10,' '); }	// *PROGRESS-BAR*

	if ($this->ColActive) { $this->table_rotate = 0; }	// *COLUMNS*
	if ($this->table_rotate <> 0) {
		$this->tablebuffer = '';
		// Max width for rotated table
		$this->tbrot_maxw = $this->h - ($this->y + $this->bMargin + 1);
		$this->tbrot_maxh = $this->blk[$this->blklvl]['inner_width'] ;		// Max width for rotated table
		$this->tbrot_align = $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['a'] ;
	}
	$this->shrin_k = 1;

	if ($this->shrink_tables_to_fit < 1) { $this->shrink_tables_to_fit = 1; }
	if (!$this->shrink_this_table_to_fit) { $this->shrink_this_table_to_fit = $this->shrink_tables_to_fit; }

	if ($this->tableLevel>1) {
		// deal with nested table

		$this->_tableColumnWidth($this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]],true);

		$tmiw = $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['miw'];
		$tmaw = $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['maw'];
		$tl = $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['tl'];

		// Go down to lower table level
		$this->tableLevel--;

		// Reset lower level table
		$this->base_table_properties = $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['baseProperties'];
		$this->cell = $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['cells'];
		// mPDF 5.4.10
		if (isset($this->cell['PARENTCELL'])) { 
			if ($this->cell['PARENTCELL']) { $this->restoreInlineProperties($this->cell['PARENTCELL']); }
			unset($this->cell['PARENTCELL']);
		}
		$this->row = $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['currrow'];
		$this->col = $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['currcol'];
		$objattr = array();
		$objattr['type'] = 'nestedtable';
		$objattr['nestedcontent'] = $this->tbctr[($this->tableLevel+1)];
		$objattr['table'] = $this->tbctr[$this->tableLevel];
		$objattr['row'] = $this->row;
		$objattr['col'] = $this->col;
		$objattr['level'] = $this->tableLevel;
		$e = "\xbb\xa4\xactype=nestedtable,objattr=".serialize($objattr)."\xbb\xa4\xac";
		$this->_saveCellTextBuffer($e);
		$this->cell[$this->row][$this->col]['s'] += $tl ;
		if (!isset($this->cell[$this->row][$this->col]['maxs'])) {
			$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s']; 
		}
		elseif($this->cell[$this->row][$this->col]['maxs'] < $this->cell[$this->row][$this->col]['s']) {
			$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s']; 
		}
		$this->cell[$this->row][$this->col]['s'] = 0;// reset
		if ((isset($this->cell[$this->row][$this->col]['nestedmaw']) && $this->cell[$this->row][$this->col]['nestedmaw'] < $tmaw) || !isset($this->cell[$this->row][$this->col]['nestedmaw'])) { $this->cell[$this->row][$this->col]['nestedmaw'] = $tmaw ; }
		if ((isset($this->cell[$this->row][$this->col]['nestedmiw']) && $this->cell[$this->row][$this->col]['nestedmiw'] < $tmiw) || !isset($this->cell[$this->row][$this->col]['nestedmiw'])) { $this->cell[$this->row][$this->col]['nestedmiw'] = $tmiw ; }
		$this->tdbegin = true;
		$this->nestedtablejustfinished = true;
		$this->ignorefollowingspaces = true;
		return;
	}
	$this->cMarginL = 0;
	$this->cMarginR = 0;
	$this->cMarginT = 0;
	$this->cMarginB = 0;
	$this->cellPaddingL = 0;
	$this->cellPaddingR = 0;
	$this->cellPaddingT = 0;
	$this->cellPaddingB = 0;

	if (isset($this->table[1][1]['overflow']) && $this->table[1][1]['overflow']=='visible') {
		if ($this->kwt || $this->table_rotate || $this->table_keep_together || $this->ColActive) { 
			$this->kwt = false;
			$this->table_rotate = 0;
			$this->table_keep_together = false;
			//die("mPDF Warning: You cannot use CSS overflow:visible together with any of these functions: 'Keep-with-table', rotated tables, page-break-inside:avoid, or columns");
		}
		$this->_tableColumnWidth($this->table[1][1],true);
		$this->_tableWidth($this->table[1][1]);
	}
	else {
	  if (!$this->kwt_saved) { $this->kwt_height = 0; }

	  list($check,$tablemiw) = $this->_tableColumnWidth($this->table[1][1],true);
	  $save_table = $this->table;	
	  if ($this->cacheTables) { $this->_backupCacheFiles(); }
	  $reset_to_minimum_width = false;
	  $added_page = false;

	  if ($check > 1) {
		if ($check > $this->shrink_this_table_to_fit && $this->table_rotate) { 
			if ($this->y != $this->tMargin) {
				$this->AddPage($this->CurOrientation);
				$this->kwt_moved = true; 
			}
			$added_page = true;
			$this->tbrot_maxw = $this->h - ($this->y + $this->bMargin + 5) - $this->kwt_height;
			//$check = $tablemiw/$this->tbrot_maxw; 	// undo any shrink
			$check = 1; 	// undo any shrink
		}
		$reset_to_minimum_width = true;
	  }

	  if ($reset_to_minimum_width) {

		$this->shrin_k = $check;

 		$this->default_font_size /= $this->shrin_k;
		$this->SetFontSize($this->default_font_size, false );

		$this->shrinkTable($this->table[1][1],$this->shrin_k);

		$this->_tableColumnWidth($this->table[1][1],false);	// repeat

		// Starting at $this->innermostTableLevel
		// Shrink table values - and redo columnWidth
		for($lvl=2;$lvl<=$this->innermostTableLevel;$lvl++) {
			for ($nid=1; $nid<=$this->tbctr[$lvl]; $nid++) {
				$this->shrinkTable($this->table[$lvl][$nid],$this->shrin_k);
				$this->_tableColumnWidth($this->table[$lvl][$nid],false);
			}
		}
	  }

	  // Set table cell widths for top level table
	  // Use $shrin_k to resize but don't change again
	  $this->SetLineHeight('',$this->table_lineheight);

	  // Top level table
	  $this->_tableWidth($this->table[1][1]);

	}


	// Now work through any nested tables setting child table[w'] = parent cell['w']
	// Now do nested tables _tableWidth
	for($lvl=2;$lvl<=$this->innermostTableLevel;$lvl++) {
		for ($nid=1; $nid<=$this->tbctr[$lvl]; $nid++) {
			// HERE set child table width = cell width

			list($parentrow, $parentcol, $parentnid) = $this->table[$lvl][$nid]['nestedpos'];

			if ($this->cacheTables) {
				$c = $this->_uncacheCell($this->table[($lvl-1)][$parentnid]['cells'][$parentrow][$parentcol], $this->table[($lvl-1)][$parentnid]['cache'], null);
			}
			else 
				$c =& $this->table[($lvl-1)][$parentnid]['cells'][$parentrow][$parentcol];

			if (isset($c['colspan']) && $c['colspan']> 1) {
			   $parentwidth = 0;
			   for($cs=0;$cs<$c['colspan'] ; $cs++) {
				$parentwidth += $this->table[($lvl-1)][$parentnid]['wc'][$parentcol+$cs]; 
			   }
			}
			else { $parentwidth = $this->table[($lvl-1)][$parentnid]['wc'][$parentcol]; }


			//$parentwidth -= ALLOW FOR PADDING ETC.in parent cell
			if (!$this->simpleTables){
			 if ($this->packTableData) {
			 	list($bt,$br,$bb,$bl) = $this->_getBorderWidths($c['borderbin']);
			 }
			 else { 
				$br = $c['border_details']['R']['w'];
				$bl = $c['border_details']['L']['w'];
			 }
			 if ($this->table[$lvl-1][$parentnid]['borders_separate']) {
			  $parentwidth -= $br + $bl
				+ $c['padding']['L']
				+ $c['padding']['R']
				+ $this->table[($lvl-1)][$parentnid]['border_spacing_H'];
			 }
			 else {
			  $parentwidth -= $br/2 + $bl/2
				+ $c['padding']['L']
				+ $c['padding']['R'];
			 }
			}
			else if ($this->simpleTables){
			 if ($this->table[$lvl-1][$parentnid]['borders_separate']) {
			  $parentwidth -= $this->table[($lvl-1)][$parentnid]['simple']['border_details']['L']['w']
				+ $this->table[($lvl-1)][$parentnid]['simple']['border_details']['R']['w']
				+ $c['padding']['L']
				+ $c['padding']['R']
				+ $this->table[($lvl-1)][$parentnid]['border_spacing_H'];
			 }
			 else {
			  $parentwidth -= $this->table[($lvl-1)][$parentnid]['simple']['border_details']['L']['w']/2
				+ $this->table[($lvl-1)][$parentnid]['simple']['border_details']['R']['w']/2
				+ $c['padding']['L']
				+ $c['padding']['R'];
			 }
			}
			if (isset($this->table[$lvl][$nid]['wpercent']) && $this->table[$lvl][$nid]['wpercent'] && $lvl>1) {
				$this->table[$lvl][$nid]['w'] = $parentwidth;
			}
			else if ($parentwidth > $this->table[$lvl][$nid]['maw']) {
				$this->table[$lvl][$nid]['w'] = $this->table[$lvl][$nid]['maw'];
			}
			else {
				$this->table[$lvl][$nid]['w'] = $parentwidth;
			}
			unset($c);
			$this->_tableWidth($this->table[$lvl][$nid]);
		}
	}

	// Starting at $this->innermostTableLevel
	// Cascade back up nested tables: setting heights back up the tree
	for($lvl=$this->innermostTableLevel;$lvl>0;$lvl--) {
		for ($nid=1; $nid<=$this->tbctr[$lvl]; $nid++) {
			list($tableheight,$maxrowheight,$fullpage,$remainingpage, $maxfirstrowheight) = $this->_tableHeight($this->table[$lvl][$nid]);			}
	}
	if ($this->progressBar) { $this->UpdateProgressBar(7,20,' '); }	// *PROGRESS-BAR*
	if ($this->table[1][1]['overflow']=='visible') {
		if ($maxrowheight > $fullpage) { die("mPDF Warning: A Table row is greater than available height. You cannot use CSS overflow:visible"); }
		if ($maxfirstrowheight > $remainingpage) { $this->AddPage($this->CurOrientation); }
		$r = 0; $c = 0; $p = 0; $y = 0;
		while (!$finished) {
			list($finished,$r,$c,$p,$y,$y0) = $this->_tableWrite($this->table[1][1],true,$r,$c,$p,$y);
			if (!$finished) { 
				$this->AddPage($this->CurOrientation); 
				// If printed something on first spread, set same y
				if ($r==0 && $y0 > -1) { $this->y = $y0; }
			}
		}
	}
	else {
	  $recalculate = 1;
	  $forcerecalc = false;
	  // RESIZING ALGORITHM
	  if ($maxrowheight > $fullpage) { 
		$recalculate = $this->tbsqrt($maxrowheight / $fullpage, 1); 
		$forcerecalc = true;
	  }
	  else if ($this->table_rotate) {	// NB $remainingpage == $fullpage == the width of the page
		if ($tableheight > $remainingpage) { 
			// If can fit on remainder of page whilst respecting autsize value..
			if (($this->shrin_k * $this->tbsqrt($tableheight / $remainingpage, 1)) <= $this->shrink_this_table_to_fit) {
				$recalculate = $this->tbsqrt($tableheight / $remainingpage, 1); 
			}
			else if (!$added_page) {
				if ($this->y != $this->tMargin) {
					$this->AddPage($this->CurOrientation);
					$this->kwt_moved = true; 
				}
				$added_page = true;
				$this->tbrot_maxw = $this->h - ($this->y + $this->bMargin + 5) - $this->kwt_height;
				// 0.001 to force it to recalculate
				$recalculate = (1 / $this->shrin_k) + 0.001; 	// undo any shrink
			}
		}
		else { $recalculate = 1; }
	  }
	  else if ($this->table_keep_together || ($this->table[1][1]['nr']==1 && !$this->writingHTMLfooter)) {	
		if ($tableheight > $fullpage) { 
			if (($this->shrin_k * $this->tbsqrt($tableheight / $fullpage, 1)) <= $this->shrink_this_table_to_fit) {
				$recalculate = $this->tbsqrt($tableheight / $fullpage, 1); 
			}
			else if ($this->tableMinSizePriority) {
				$this->table_keep_together = false; 
				$recalculate = 1.001; 
			}
			else {
				if ($this->y != $this->tMargin) {	// mPDF 5.1
					$this->AddPage($this->CurOrientation);
					$this->kwt_moved = true; 
				}
				$added_page = true;
				$this->tbrot_maxw = $this->h - ($this->y + $this->bMargin + 5) - $this->kwt_height;
				$recalculate = $this->tbsqrt($tableheight / $fullpage, 1); 
			}
		}
		else if ($tableheight > $remainingpage) { 
			// If can fit on remainder of page whilst respecting autsize value..
			if (($this->shrin_k * $this->tbsqrt($tableheight / $remainingpage, 1)) <= $this->shrink_this_table_to_fit) {
				$recalculate = $this->tbsqrt($tableheight / $remainingpage, 1); 
			}
			else {
				if ($this->y != $this->tMargin) {
					$this->AddPage($this->CurOrientation);
					$this->kwt_moved = true;
				} 
				$added_page = true;
				$this->tbrot_maxw = $this->h - ($this->y + $this->bMargin + 5) - $this->kwt_height;
				$recalculate = 1.001; 
			}
		}
		else { $recalculate = 1; }
	  }
	  else { $recalculate = 1; }

	  if ($recalculate > $this->shrink_this_table_to_fit && !$forcerecalc) { $recalculate = $this->shrink_this_table_to_fit; } 

	  $iteration = 1;

	  // RECALCULATE
	  while($recalculate <> 1) {
		$this->shrin_k1 = $recalculate ;
		$this->shrin_k *= $recalculate ;
 		$this->default_font_size /= ($this->shrin_k1) ;
		$this->SetFontSize($this->default_font_size, false );
		$this->SetLineHeight('',$this->table_lineheight);
		$this->table = $save_table;
		if ($this->cacheTables) { $this->_restoreCacheFiles(); }
		if ($this->shrin_k <> 1) { $this->shrinkTable($this->table[1][1],$this->shrin_k); }
		$this->_tableColumnWidth($this->table[1][1],false);	// repeat

		// Starting at $this->innermostTableLevel
		// Shrink table values - and redo columnWidth
		for($lvl=2;$lvl<=$this->innermostTableLevel;$lvl++) {
			for ($nid=1; $nid<=$this->tbctr[$lvl]; $nid++) {
				if ($this->shrin_k <> 1) { $this->shrinkTable($this->table[$lvl][$nid],$this->shrin_k); }
				$this->_tableColumnWidth($this->table[$lvl][$nid],false);
			}
		}
		// Set table cell widths for top level table

		// Top level table
		$this->_tableWidth($this->table[1][1]);

		// Now work through any nested tables setting child table[w'] = parent cell['w']
		// Now do nested tables _tableWidth
		for($lvl=2;$lvl<=$this->innermostTableLevel;$lvl++) {
			for ($nid=1; $nid<=$this->tbctr[$lvl]; $nid++) {
				// HERE set child table width = cell width

				list($parentrow, $parentcol, $parentnid) = $this->table[$lvl][$nid]['nestedpos'];
				if ($this->cacheTables) {
					$c = $this->_uncacheCell($this->table[($lvl-1)][$parentnid]['cells'][$parentrow][$parentcol], $this->table[($lvl-1)][$parentnid]['cache'], null);
				}
				else 
					$c =& $this->table[($lvl-1)][$parentnid]['cells'][$parentrow][$parentcol];

				if (isset($c['colspan']) && $c['colspan']> 1) {
				   $parentwidth = 0;
				   for($cs=0;$cs<$c['colspan'] ; $cs++) {
					$parentwidth += $this->table[($lvl-1)][$parentnid]['wc'][$parentcol+$cs]; 
				   }
				}
				else { $parentwidth = $this->table[($lvl-1)][$parentnid]['wc'][$parentcol]; }

				//$parentwidth -= ALLOW FOR PADDING ETC.in parent cell
				if (!$this->simpleTables){
				 if ($this->packTableData) {
				 	list($bt,$br,$bb,$bl) = $this->_getBorderWidths($c['borderbin']);
				 }
				 else { 
					$br = $c['border_details']['R']['w'];
					$bl = $c['border_details']['L']['w'];
				 }
				 if ($this->table[$lvl-1][$parentnid]['borders_separate']) {
				  $parentwidth -= $br + $bl
					+ $c['padding']['L']
					+ $c['padding']['R']
					+ $this->table[($lvl-1)][$parentnid]['border_spacing_H'];
				 }
				 else {
				  $parentwidth -= $br/2 + $bl/2
					+ $c['padding']['L']
					+ $c['padding']['R'];
				 }
				}
				else if ($this->simpleTables){
				 if ($this->table[$lvl-1][$parentnid]['borders_separate']) {
				  $parentwidth -= $this->table[($lvl-1)][$parentnid]['simple']['border_details']['L']['w']
					+ $this->table[($lvl-1)][$parentnid]['simple']['border_details']['R']['w']
					+ $c['padding']['L']
					+ $c['padding']['R']
					+ $this->table[($lvl-1)][$parentnid]['border_spacing_H'];
				 }
				 else {
				  $parentwidth -= ($this->table[($lvl-1)][$parentnid]['simple']['border_details']['L']['w']
					+ $this->table[($lvl-1)][$parentnid]['simple']['border_details']['R']['w']) /2
					+ $c['padding']['L']
					+ $c['padding']['R'];
				 }
				}
				if (isset($this->table[$lvl][$nid]['wpercent']) && $this->table[$lvl][$nid]['wpercent'] && $lvl>1) {
					$this->table[$lvl][$nid]['w'] = $parentwidth;
				}
				else if ($parentwidth > $this->table[$lvl][$nid]['maw']) {
					$this->table[$lvl][$nid]['w'] = $this->table[$lvl][$nid]['maw'] ;
				}
				else {
					$this->table[$lvl][$nid]['w'] = $parentwidth;
				}
				unset($c);
				$this->_tableWidth($this->table[$lvl][$nid]);
			}
		}

		// Starting at $this->innermostTableLevel
		// Cascade back up nested tables: setting heights back up the tree
		for($lvl=$this->innermostTableLevel;$lvl>0;$lvl--) {
			for ($nid=1; $nid<=$this->tbctr[$lvl]; $nid++) {
				list($tableheight,$maxrowheight,$fullpage,$remainingpage, $maxfirstrowheight) = $this->_tableHeight($this->table[$lvl][$nid]);			}
		}

		// RESIZING ALGORITHM

		if ($maxrowheight > $fullpage) { $recalculate = $this->tbsqrt($maxrowheight / $fullpage, $iteration); $iteration++; }
		else if ($this->table_rotate && $tableheight > $remainingpage && !$added_page) { 
			// If can fit on remainder of page whilst respecting autosize value..
			if (($this->shrin_k * $this->tbsqrt($tableheight / $remainingpage, $iteration)) <= $this->shrink_this_table_to_fit) {
				$recalculate = $this->tbsqrt($tableheight / $remainingpage, $iteration); $iteration++; 
			}
			else {
				if (!$added_page) { 
					$this->AddPage($this->CurOrientation); 
					$added_page = true;
					$this->kwt_moved = true; 
					$this->tbrot_maxw = $this->h - ($this->y + $this->bMargin + 5) - $this->kwt_height;
				}
				// 0.001 to force it to recalculate
				$recalculate = (1 / $this->shrin_k) + 0.001; 	// undo any shrink
			}
		}
		else if ($this->table_keep_together || ($this->table[1][1]['nr']==1 && !$this->writingHTMLfooter)) {
			if ($tableheight > $fullpage) { 
				if (($this->shrin_k * $this->tbsqrt($tableheight / $fullpage, $iteration)) <= $this->shrink_this_table_to_fit) {
					$recalculate = $this->tbsqrt($tableheight / $fullpage, $iteration); $iteration++; 
				}
				else if ($this->tableMinSizePriority) {
					$this->table_keep_together = false; 
					$recalculate = (1 / $this->shrin_k) + 0.001; 
				}
				else {
				   if (!$added_page && $this->y != $this->tMargin) {
					$this->AddPage($this->CurOrientation);
					$added_page = true;
					$this->kwt_moved = true; 
					$this->tbrot_maxw = $this->h - ($this->y + $this->bMargin + 5) - $this->kwt_height;
				   }
				   $recalculate = $this->tbsqrt($tableheight / $fullpage, $iteration); $iteration++; 
				}
			}
			else if ($tableheight > $remainingpage) { 
				// If can fit on remainder of page whilst respecting autosize value..
				if (($this->shrin_k * $this->tbsqrt($tableheight / $remainingpage, $iteration)) <= $this->shrink_this_table_to_fit) {
					$recalculate = $this->tbsqrt($tableheight / $remainingpage, $iteration);  $iteration++; 
				}
				else {
					if (!$added_page) { 
						$this->AddPage($this->CurOrientation); 
						$added_page = true;
						$this->kwt_moved = true; 
						$this->tbrot_maxw = $this->h - ($this->y + $this->bMargin + 5) - $this->kwt_height;
					}
				 
					//$recalculate = $this->tbsqrt($tableheight / $fullpage, $iteration); $iteration++; 
					$recalculate = (1 / $this->shrin_k) + 0.001; 	// undo any shrink
				}
			}
			else { $recalculate = 1; }
		}
		else { $recalculate = 1; }
	  }


	  if ($maxfirstrowheight > $remainingpage && !$added_page && !$this->table_rotate && !$this->ColActive && !$this->table_keep_together && !$this->writingHTMLheader && !$this->writingHTMLfooter) {
		$this->AddPage($this->CurOrientation); 
		$this->kwt_moved = true;
	  }

	  // keep-with-table: if page has advanced, print out buffer now, else done in fn. _Tablewrite()
	  if ($this->kwt_saved && $this->kwt_moved) {
		$this->printkwtbuffer();
		$this->kwt_moved = false;
		$this->kwt_saved = false;
	  }

	  if ($this->progressBar) { $this->UpdateProgressBar(7,30,' '); }	// *PROGRESS-BAR*
	  // Recursively writes all tables starting at top level
	  $this->_tableWrite($this->table[1][1]);

	  if ($this->table_rotate && $this->tablebuffer) {
		$this->PageBreakTrigger=$this->h-$this->bMargin;
		$save_tr = $this->table_rotate;
		$save_y = $this->y;
		$this->table_rotate = 0;
		$this->y = $this->tbrot_y0;
		$h = 	$this->tbrot_w;
		$this->DivLn($h,$this->blklvl,true);

		$this->table_rotate = $save_tr;
		$this->y = $save_y;

		$this->printtablebuffer();
	  }
	  $this->table_rotate = 0;
	}


	$this->x = $this->lMargin + $this->blk[$this->blklvl]['outer_left_margin'];

	$this->maxPosR = max($this->maxPosR , ($this->x + $this->table[1][1]['w'])); 

	$this->blockjustfinished=true;
	$this->lastblockbottommargin = $this->table[1][1]['margin']['B'];
	//Reset values

	if (isset($this->table[1][1]['page_break_after'])) { $page_break_after = $this->table[1][1]['page_break_after']; }
	else { $page_break_after = ''; }

	// Keep-with-table
	$this->kwt = false;
	$this->kwt_y0 = 0;
	$this->kwt_x0 = 0;
	$this->kwt_height = 0;
	$this->kwt_buffer = array();
	$this->kwt_Links = array();
	$this->kwt_Annots = array();
	$this->kwt_moved = false;
	$this->kwt_saved = false;

	$this->kwt_Reference = array();
	$this->kwt_BMoutlines = array();
	$this->kwt_toc = array();

	$this->shrin_k = 1;
	$this->shrink_this_table_to_fit = 0;

	unset($this->table);
	$this->table=array(); //array 
	$this->tableLevel=0;
	$this->tbctr=array();
	$this->innermostTableLevel=0;
	$this->cssmgr->tbCSSlvl = 0;
	$this->cssmgr->tablecascadeCSS = array();

	unset($this->cell);
	$this->cell=array(); //array 

	$this->col=-1; //int
	$this->row=-1; //int
	$this->Reset();

 	$this->cellPaddingL = 0;
 	$this->cellPaddingT = 0;
 	$this->cellPaddingR = 0;
 	$this->cellPaddingB = 0;
 	$this->cMarginL = 0;
 	$this->cMarginT = 0;
 	$this->cMarginR = 0;
 	$this->cMarginB = 0;
 	$this->default_font_size = $this->original_default_font_size;
	$this->default_font = $this->original_default_font;
   	$this->SetFontSize($this->default_font_size, false);
	$this->SetFont($this->default_font,'',0,false);
	$this->SetLineHeight();
	if (isset($this->blk[$this->blklvl]['InlineProperties'])) { $this->restoreInlineProperties($this->blk[$this->blklvl]['InlineProperties']);}
	if ($this->progressBar) { $this->UpdateProgressBar(7,100,' '); }	// *PROGRESS-BAR*

	if ($page_break_after) {
		$save_blklvl = $this->blklvl;
		$save_blk = $this->blk;
		$save_silp = $this->saveInlineProperties();
		$save_spanlvl = $this->spanlvl;
		$save_ilp = $this->InlineProperties;
		if ($this->blklvl>1) {
			// Close any open block tags
			for ($b= $this->blklvl;$b>0;$b--) { $this->CloseTag($this->blk[$b]['tag']); }
			// Output any text left in buffer
			if (count($this->textbuffer)) { $this->printbuffer($this->textbuffer); $this->textbuffer=array(); }
		}
/*-- COLUMNS --*/
		$save_cols = false;
		if ($this->ColActive) {
			$save_cols = true;
			$save_nbcol = $this->NbCol;	// other values of gap and vAlign will not change by setting Columns off
			$this->SetColumns(0);
		}
/*-- END COLUMNS --*/
		if ($page_break_after == 'RIGHT') { $this->AddPage($this->CurOrientation,'NEXT-ODD','','','','','', '','', '','','','','','',0,0,0,0,$pagesel); }
		else if ($page_break_after == 'LEFT') { $this->AddPage($this->CurOrientation,'NEXT-EVEN','','','','','', '','', '','','','','','',0,0,0,0,$pagesel); }
		else { $this->AddPage($this->CurOrientation,'','','','','','', '','', '','','','','','',0,0,0,0,$pagesel); }
		if (!$this->restoreBlockPagebreaks) {
			$this->blklvl = 0;
			$this->lastblocklevelchange = 0;
			$this->blk = array();
			$this->initialiseBlock($this->blk[0]);
			$this->blk[0]['width'] =& $this->pgwidth;
			$this->blk[0]['inner_width'] =& $this->pgwidth;
			$this->blk[0]['blockContext'] = $this->blockContext;
			$properties = $this->cssmgr->MergeCSS('BLOCK','BODY','');
			$this->setCSS($properties,'','BODY'); 
		}

/*-- COLUMNS --*/
		if ($save_cols) {
			// Restore columns
			$this->SetColumns($save_nbcol,$this->colvAlign,$this->ColGap);
		}
/*-- END COLUMNS --*/
		if ($this->restoreBlockPagebreaks) {
			$this->blk = $save_blk;
			// Re-open block tags
			$t = $this->blk[0]['tag'];
			$a = $this->blk[0]['attr'];
			$this->blklvl = 0; 
			for ($b=0; $b<=$save_blklvl;$b++) {
				$tc = $t;
				$ac = $a;
				$t = $this->blk[$b+1]['tag'];
				$a = $this->blk[$b+1]['attr'];
				unset($this->blk[$b+1]);
				$this->OpenTag($tc,$ac); 
			}
			$this->spanlvl = $save_spanlvl;
			$this->InlineProperties = $save_ilp;
			$this->restoreInlineProperties($save_silp);
		}
	}

    }
/*-- END TABLES --*/

/*-- LISTS --*/
	// *********** LISTS ********************

    if($tag=='LI') { 
	$this->lastoptionaltag = ''; 
	unset($this->cssmgr->listcascadeCSS[$this->cssmgr->listCSSlvl]);
	$this->cssmgr->listCSSlvl--;
	if (isset($this->listoccur[$this->listlvl]) && isset($this->InlineProperties['LIST'][$this->listlvl][$this->listoccur[$this->listlvl]])) { $this->restoreInlineProperties($this->InlineProperties['LIST'][$this->listlvl][$this->listoccur[$this->listlvl]]); } 
    }


    if(($tag=='UL') or ($tag=='OL')) {
      $this->ignorefollowingspaces = true; //Eliminate exceeding left-side spaces
	unset($this->cssmgr->listcascadeCSS[$this->cssmgr->listCSSlvl]);
	$this->cssmgr->listCSSlvl--;

	$this->lastoptionaltag = '';
/*-- TABLES --*/
	// A simple list for inside a table
	if($this->tableLevel) {
		$this->listlist[$this->listlvl]['MAXNUM'] = $this->listnum; //save previous lvl's maxnum
		unset($this->listlist[$this->listlvl]);
		$this->listlvl--;
		if (isset($this->listlist[$this->listlvl]['MAXNUM'])) { $this->listnum = $this->listlist[$this->listlvl]['MAXNUM']; } // restore previous levels
		if ($this->listlvl == 0) { $this->listjustfinished = true; }
		return;
	}
/*-- END TABLES --*/

	if ($this->listlvl > 1) { // returning one level
		$this->listjustfinished=true;
		if (!empty($this->textbuffer)) { 
			$this->listitem[] = array($this->listlvl,$this->listnum,$this->textbuffer,$this->listoccur[$this->listlvl],$this->listitemtype);
		}
		else { 
			$this->listnum--;
		}

		$this->textbuffer = array();
		$occur = $this->listoccur[$this->listlvl];
		$this->listlist[