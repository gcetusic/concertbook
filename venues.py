# -*- coding: utf-8 -*-
import sys
import foursquare

from settings import FOURSQUARE_KEY, FOURSQUARE_SECRET, EVENT_RADIUS


def get_similar_venues(event_location, location):
    """
    Given an event and a location of a venue, return list of similar venues in
    proximity to event location.
    """
    venue_list = []
    fsquare = foursquare.Foursquare(
        client_id=FOURSQUARE_KEY, client_secret=FOURSQUARE_SECRET)
    try:
        # fetch the id of first location venue found
        vid = fsquare.venues.search(
            params={
                'limit': 5,
                'll': location,
                'query': 'music,concert'
            })['venues'][0]['id']
        # extract categories of location
        category_ids = [category['id'] for category in
            fsquare.venues(vid)['venue']['categories']]
        # search for venues in the same category in proximity to event
        venues = fsquare.venues.search(
            params={
                'radius': EVENT_RADIUS * 1000,
                'limit': 5,
                'll': event_location,
                'categoryId': ','.join(category_ids)
            })['venues']
        for venue in venues:
            venue_list.append(
                {
                    'name': venue['name'],
                    'location': venue['location']['formattedAddress'],
                    'latitude': venue['location']['lat'],
                    'longitude': venue['location']['lng'],
                }
            )
    except:
        pass
    return venue_list


def get_close_venues(location):
    # (location['geo:lat'], location['geo:long'])
    venue_list = []
    fsquare = foursquare.Foursquare(
        client_id=FOURSQUARE_KEY, client_secret=FOURSQUARE_SECRET)
    try:
        # search pizza and coffee places in proximity to event
        venues = fsquare.venues.search(params={
            'query': 'pizza,coffee',
            'll': location,
            'intent': 'browse',
            'radius': EVENT_RADIUS * 1000,
        })['venues']
        for venue in venues:
            venue_list.append(
                {
                    'name': venue['name'],
                    'location': venue['location']['formattedAddress'],
                    'latitude': venue['location']['lat'],
                    'longitude': venue['location']['lng'],
                }
            )
    except:
        pass
    return venue_list

if __name__ == '__main__':
    location = sys.argv[1]
    print(get_close_venues(location))
