<?php // (C) Copyright Bobbing Wide 2016-2017

/**
 * Map IP addresses to host name using a cached file for previously discovered IPs
 *
 * The oik-bwtrace daily trace summary file records the IP address of the requester
 * Until Jan 2016 it didn't record the HTTP_USER_AGENT, which CAN be used to determine
 * who's making the request. 
 * Consequently, all requests are counted, even those from bots, crawlers and spammers.
 *
 * But we can map the IP address to a host name and then infer more information from that
 * classifying the request as "bot", "crawler", "spammer", "visitor" 
 * in a similar way to AWstats.
 *
 * So why not just use AWstats ( www.awstats.org ). Because it's written in Perl.
 *
 *
 
 * 
 * 
 * 
 */
 
class IP_to_host extends Object_base {

	/**
	 * File name for saving intermediate files
	 *
	 * Note: It is extremely inefficient to save the file for every new IP address
	 * In a 35K access log with over 2.5K different IPs the execution time was 5,000 seconds!
	 * It must be the serialization time.
	 * 
	 */

	public $saver=null;
	
	/**
	 * Instance of the Browscap class which can be used to convert
	 * the user_agent information to two simple fields: Browser and Crawler
	 */
	public $browscap = null;


	/**
	 * Constructor for IP_to_host
	 */
	function __construct() {
		parent::__construct();
		$this->saver();
		$this->browscap();
	}
	
	
	/**
	 * Map an IP address to a host name
	 *
	 * If we get a new IP then update $saver, if set
	 */
	function IP_to_host( $ip, $http_user_agent=null ) {
		//$s = microtime( true );
		$ip_array = array_column( $this->objects, "ip" );
		$key = array_search( $ip, $ip_array );
		if ( $key !== false )  {
		
			$map = $this->objects[ $key ];
			//print_r( $map );
			$host = $map['host'];
			$this->increment( $key );
			
		} else {
			//echo "Did not find $ip" . PHP_EOL;
			$host = $this->_IP_to_host( $ip );
			$this->add( $ip, $host, $http_user_agent );
			if ( $this->saver ) {
				$this->save_to_file( $this->saver );
			}
		}
		//$e = microtime( true );
		//$el = sprintf( "%.6f", $e - $s );
		//echo "$el $ip  $host" . PHP_EOL;
		//echo PHP_EOL;
		
		return( $host );
	}
	
	/**
	 * get host by addr
	 * 
	 * 
	 */
	function _IP_to_host( $ip ) {
		$host = gethostbyaddr( $ip );
		return( $host );
	}
	
	function add( $ip, $host, $http_user_agent=null ) {
		$object = array( "ip" => $ip, "host" => $host, "count" => 1 );
		
		if ( $this->browscap ) {
			$browser = $this->browscap->getBrowser( $http_user_agent );
			$object["browser"] = $browser->Browser;
			$object["crawler"] = $browser->Crawler; 
		} else {
		}
		$object["user_agent"] = $http_user_agent;
		
			
		//print_r( $object );
		$this->objects[] = $object;
	}
	
	function report() {
		echo count( $this->objects ) . PHP_EOL;
		if ( count( $this->objects ) ) {
			foreach ( $this->objects as $key => $object ) {
				$line = array();
				//$line[] = $key;
				$line[] = $object['count'];
				$line[] = $object['ip'];
				$line[] = $object['host'];
				if ( !isset( $object['crawler'] ) ) {
					$line[] = "";
					$line[] = "";
					
				}	else {
					$line[] = $object['crawler'];
					$line[] = $object['browser'];
				}
				if ( isset( $object['user_agent'] ) ) {
				
					$line[] = $object['user_agent'];
				} else {
					$line[] = "";
				}
				$oline = implode( $line, "," );
				
				echo $oline . PHP_EOL;
				
			}
		}
		//print_r( $this->objects );
	}
	
	function saver( $saver=null ) {
		$this->saver = $saver;
	}
	
	function browscap( $browscap=null ) {
		$this->browscap = $browscap;
	}
	
	function increment( $key ) {
		$this->objects[ $key ]['count'] += 1;
	}
	
	
	/**
	 * Return a simple classification for the client
	 * given the IP, hostname and HTTP_USER_AGENT values
	 *
	 *
	 * Classifications:
	 * 
	 * 
	 */ 
	function classify( $ip, $host, $http_user_agent ) {
	
	}
	
	
	/**
	 * Determine browser or crawler
	 * 
	 */
	function crawlers( $browscap ) {
	
		echo count( $this->objects ) . PHP_EOL;
		if ( count( $this->objects ) ) {
			foreach ( $this->objects as $key => $object ) {
				$browser = $browscap->getBrowser( $object['user_agent'] );
				
				$line = array();
				$line[] = $key;
				$line[] = $object['ip'];
				$line[] = $object['host'];
				$line[] = $browser->Browser;
				$line[] = $browser->Crawler;
				$line[] = $object['count'];
				$oline = implode( $line, "," );
				
				echo $oline . PHP_EOL;
				
			}
		}
		//print_r( $this->objects );
	}
	
		
		
	


}
