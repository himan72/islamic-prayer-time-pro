						<?php
						/**------------------------------------------------------------------------
						# Islamic Prayer Time Pro module by MH Oudrhiri, joomlar.net
						# ------------------------------------------------------------------------
						# author    M Hicham Oudrhiri http://www.joomlar.net/
						# Copyright @2014 Joomlar.net.  All Rights Reserved.
						# @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
						# Website: http://www.joomlar.net/
						-------------------------------------------------------------------------*/

						// No direct access
						defined( '_JEXEC' ) or die;

						// Include the syndicate functions only once
						require_once __DIR__ . '/helper.php';

						// Getting the current year and month to inject in the url
						$year  										 	 = date('Y');
						$month 										 	 = date('m');
						$day   										 	 = date('j');

						// Getting the city Name, latitude & longitude
						$latitude                    = $params->get ('latitude', 21.4267);
						$longitude                   = $params->get ('longitude', 39.8261);
						$timezone                    = $params->get ('timezone', 'Makkah');
						$country                     = $params->get ('country', 'Saudia Arabia');
						$city                        = $params->get ('city', 'Makkah');

						//Getting the Prayer features
						$calculmethod                = $params->get ('calculmethod', 4);
						$asrjurictic                 = $params->get ('asr_juristic_method', 0);
						$highlight                   = $params->get ('adjmethod', 0);
						$fajrangle                   = $params->get ('fajrangle');
						$timeformat                  = $params->get ('prayer_time_format',0);

						// Prayer timae manual adjustment
						$summertime                  = $params->get ('summertime',0);
						$daylight                    = 0;
						if ($summertime>0){$daylight = 60;}
						$fajradjt                    = $params->get ('fajraddminute',0);
						$duhradjt                    = $params->get ('duhraddminute',0);
						$asradjt                     = $params->get ('asraddminute',0);
						$maghribadjt                 = $params->get ('maghribraddminute',0);
						$ishaaadjt                   = $params->get ('ishaaddminute',0);

						// creating Prayer time object   getDatePrayerTimes (year, month, day, latitude, longitude, timeZone)
						$prayTime 									 = new PrayTime();
						// Setting up the prayer calc method
						$prayTime->setCalcMethod($calculmethod);
						$prayTime->setAsrMethod($asrjurictic);
						$prayTime->setHighLatsMethod ($highlight);
						if ($fajrangle){ $prayTime->setFajrAngle ($fajrangle);}
						$prayTime->setTimeFormat ($timeformat);

						// Manual prayers time adjusment
						$prayTime->setFajrAdjt($fajradjt+$daylight);
						$prayTime->setDhuhrMinutes($duhradjt+$daylight);
						$prayTime->setAsrAdjt($asradjt+$daylight);

						// Maghrib Prayer time Manual adjusment
						if ($calculmethod 					 ==0 || $calculmethod==7){
						$prayTime->setMaghribAdjt($maghribadjt+$daylight);
						}
						else {
						$prayTime->setMaghribMinutes($maghribadjt+$daylight);
						}

						// Ishaa Manual adjusment
						if($calculmethod==4){
						$prayTime->setIshaMinutes (90+$ishaaadjt-$maghribadjt);
						}
						else {
						$prayTime->setIshaaAdjt($ishaaadjt+$daylight);
						}

						// Chourouk Day light saving
						$prayTime->setChouroukAdjt($daylight);

						// rendering date prayer times
						$times 											 = $prayTime->getDatePrayerTimes ($year, $month, $day, $latitude, $longitude, $timezone);

						//DModule isplay features
						$fajrchk     								 = $params->get ('fajr_clbl');
						$chouroukchk 								 = $params->get ('chourouk_clbl');
						$dhurchk     								 = $params->get ('duhr_clbl');
						$asrchk      								 = $params->get ('asr_clbl');
						$maghribchk  								 = $params->get ('maghrib_clbl');
						$ishaachk    								 = $params->get ('ishaa_clbl');
						$ikamachk    								 = $params->get ('ikama_clbl', 'Ikama');
						$athanchk    								 = $params->get ('athan_clbl');
						$ikamaon   								     = $params->get ('ikama_on');


						//Ikama Time
						$fajrikama = $params->get('fajraikama', 0);
						$duhrikama = $params->get('duhrikama', 0);
						$asrikama = $params->get('asrikama', 0);
						$maghribikama = $params->get('maghribikama', 0);
						$ishaikama = $params->get('ishaikama', 0);
						$athancheck = $params->get('athan_clbl');
						$ikamacheck = $params->get('ikama_clbl');




						// Calcul method label to display on module
						if ($calculmethod 	 				 ==0) {$methodlbl=JText::_('MOD_ISLAMIC_PRAYER_TIME_METHOD_ITHNA');}
						elseif($calculmethod 				 ==1){$methodlbl=JText::_('MOD_ISLAMIC_PRAYER_TIME_METHOD_KARACHI');}
						elseif($calculmethod 				 ==2){$methodlbl=JText::_('MOD_ISLAMIC_PRAYER_TIME_METHOD_ISNA');}
						elseif($calculmethod 				 ==3){$methodlbl=JText::_('MOD_ISLAMIC_PRAYER_TIME_METHOD_MWL');}
						elseif($calculmethod 				 ==4){$methodlbl=JText::_('MOD_ISLAMIC_PRAYER_TIME_METHOD_MAKKAH');}
						elseif($calculmethod 				 ==5){$methodlbl=JText::_('MOD_ISLAMIC_PRAYER_TIME_METHOD_EGYPT');}
						elseif($calculmethod 				 ==6){$methodlbl=JText::_('MOD_ISLAMIC_PRAYER_TIME_METHOD_CUSTOM');}
						elseif($calculmethod 				 ==7){$methodlbl=JText::_('MOD_ISLAMIC_PRAYER_TIME_METHOD_THERAN');}
						elseif($calculmethod 				 ==8){$methodlbl=JText::_('MOD_ISLAMIC_PRAYER_TIME_METHOD_UNION_FRANCE');}
						// Asr jurictic to  display on module
						if($asrjurictic							 ==0){$asrjuirclbl=JText::_('MOD_ISLAMIC_PRAYER_TIME_ASR_CHAFI');}else{$asrjuirclbl=JText::_('MOD_ISLAMIC_PRAYER_TIME_ASR_HANAFI');}
						// Getting custom width & height module
						$cwidth  										 = $params->get ('module_width');
						$cheight 										 = $params->get ('module_height');

						// Getting the Hijri date params from the module.
						$offset											 = $params->get ('offset', 0);
						$lang												 = $params->get('hijrilang', "ar");
						$fsize											 = $params->get('hfsize',14);

						// Setting the date.
						$dd													 = getdate();

						// Getting the Hijri date object.
						$hdates											 = new hijriDates($dd, $offset);

						// The hijri date in arabic
						$arabichdate										 = $hdates->hijriArabic();

						// The Hijri date in Latins
						$latinhdate										 = $hdates->hijriLatin();

						// Setting the Hiri day Textuel representation
						$jlng 									 		= JFactory::getlanguage()->gettag();
						if ($jlng 							 		== "ar-AA"){
							$dhj=$lathdate[3];
						}
						else {
							$wed 									 		=date("w");
							$lhday									 	= JFactory::getDate();
							$lhday								 		= $lhday::dayToString($wed);
							$dhj 									 		= $lhday;
						}
						//Joomla coding
						require JModuleHelper::getLayoutPath('mod_islamic_prayer_time_pro', $params->get('layout',"dailytable" ));

