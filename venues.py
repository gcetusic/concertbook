# -*- coding: utf-8 -*-
import sys
import json

import foursquare
from pymongo import MongoClient
from pymongo.errors import PyMongoError
from bson.objectid import ObjectId

from settings import FOURSQUARE_KEY, FOURSQUARE_SECRET, EVENT_RADIUS


def get_similar_venues(event_location, location):
    venue_list = []
    fsquare = foursquare.Foursquare(
        client_id=FOURSQUARE_KEY, client_secret=FOURSQUARE_SECRET)
    try:
        vid = fsquare.venues.search(
            params={
                'limit': 5,
                'll': location,
                'query': 'music,concert'
            })['venues'][0]['id']
        category_ids = [category['id'] for category in
            fsquare.venues(vid)['venue']['categories']]
        venues = fsquare.venues.similar(vid)['similarVenues']['items']
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

    # client = MongoClient(DB_HOST, DB_PORT)
    # db = client[DB_NAME]
    # artists = db['artists']
    # try:
    #     artist_id = artists.insert(artist_info)
    #     artist = artists.find_one({"_id": ObjectId(artist_id)})
    # except PyMongoError as e:
    #     print "Couldn't save data", e
    #     raise
    # print(artist)
    # print(artist.value['events'][0]['venues'][0]['name'])

if __name__ == '__main__':
    location = sys.argv[1]
    print(get_close_venues(location))
