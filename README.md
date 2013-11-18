Snapsearch Client PHP Generic
=============================

Snapsearch Client PHP Generic is PHP based framework agnostic HTTP client library for SnapSearch.


Installation
------------

Usage
-----

It is PSR-0 compatible.


Looks like Robots.json will be kept in the repos themselves and just duplicated whenever there's a change.

Check how to handle API limits and Authorization!


		if($this->request->query->has('_escaped_fragment_')){

			//this becomes an array of the current query parameters
			$query_parameters = $this->request->query->all();
			//remove the _escaped_fragment_
			unset($query_parameters['_escaped_fragment_']);
			$new_query_string = '';
			//there could be no more query parameters
			if(!empty($query_parameters)){

				//urlencode the keys and values
				$new_query_parameters = array();
				foreach($query_parameters as $key => $value){
					$new_query_parameters[] = urlencode($key) . '=' . urlencode($value);
				}
				$new_query_string = '?' . implode('&', $new_query_parameters);
			}

			$hash = $this->request->query->get('_escaped_fragment_');
			$hash_fragment = '';
			if(!empty($hash)){
				$hash_fragment = '#!' . $hash;
			}

			//assemble the new url
			//we need the url BUT NOT including any of the query parameters

			//does this include the PORT?
			$new_url = $this->request->getSchemeAndHttpHost() . $this->request->getBaseUrl() . $this->request->getPathInfo() . $new_query_string . $hash_fragment;

			//WOOT!
			var_dump($new_url);

		}