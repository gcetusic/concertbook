# -*- coding: utf-8 -*-
import sys
import pylast
import foursquare
from pymongo import MongoClient
from pymongo.errors import PyMongoError
from bson.objectid import ObjectId

from settings import (
    LASTFM_SECRET, LASTFM_KEY,
    DB_HOST, DB_PORT, DB_NAME,
    FOURSQUARE_KEY, FOURSQUARE_SECRET)


def main():
    artist_name = sys.argv[1]
    client = foursquare.Foursquare(
        client_id=FOURSQUARE_KEY, client_secret=FOURSQUARE_SECRET)
    network = pylast.LastFMNetwork(api_key=LASTFM_KEY, api_secret=LASTFM_SECRET)
    artist = network.get_artist(artist_name)
    artist_info = {
        #'_id': ObjectId(str(artist.get_mbid())),
        'name': artist.get_name(),
        'events': []
    }
    for event in artist.get_upcoming_events():
        try:
            location = event.get_venue().get_location()['geo:point']
            venues = client.venues.search(params={
                'query': 'pizza,coffee',
                'll': "%s,%s" % (location['geo:lat'], location['geo:long']),
                'intent': 'browse',
                'radius': '1000',
            })['venues']
            venue_list = []
            for venue in venues:
                venue_list.append(
                    {
                        'name': venue['name'],
                        'location': venue['location']['formattedAddress'],
                    }
                )
            artist_info['events'].append({
                'title': event.get_title(),
                'start': event.get_start_date(),
                'url': event.get_url(),
                'location': "%s,%s" % (
                    location['geo:lat'], location['geo:long']),
                'venues': venue_list
            })
        except:
            pass

    client = MongoClient(DB_HOST, DB_PORT)
    db = client[DB_NAME]
    artists = db['artists']
    try:
        artist_id = artists.insert(artist_info)
        artist = artists.find_one({"_id": ObjectId(artist_id)})
    except PyMongoError as e:
        print "Couldn't save data", e
        raise
    print(artist)
    # print(artist.value['events'][0]['venues'][0]['name'])

if __name__ == '__main__':
    main()
