# -*- coding: utf-8 -*-
import sys
import json
import pylast

from settings import LASTFM_SECRET, LASTFM_KEY

from tags import get_artist_tags


def get_similar_artists(artists):
    similar_list = []
    network = pylast.LastFMNetwork(api_key=LASTFM_KEY, api_secret=LASTFM_SECRET)
    for artist in artists:
        try:
            lastfm_artist = network.get_artist(artist)
            artist_info = {
                'name': lastfm_artist.get_name(),
                'similar': [{
                    'name': similar_artist.item.get_name(),
                } for similar_artist in lastfm_artist.get_similar(limit=5)]
            }
            similar_list.append(artist_info)
        except:
            pass
    return similar_list


def get_artist_events(artists):
    event_list = []
    network = pylast.LastFMNetwork(api_key=LASTFM_KEY, api_secret=LASTFM_SECRET)
    for artist in artists:
        try:
            lastfm_artist = network.get_artist(artist)
            artist_info = {
                'name': lastfm_artist.get_name(),
                'events': [{
                    'title': event.get_title(),
                    'start': event.get_start_date(),
                    'url': event.get_url(),
                    'venue': {
                        'city': event.get_venue().location['city'],
                        'country': event.get_venue().location['country'],
                        'location': event.get_venue().location['geo:point']
                    }
                } for event in lastfm_artist.get_upcoming_events()]
            }
            event_list.append(artist_info)
        except:
            pass
    return event_list


def get_artists(artists):
    artist_list = []
    network = pylast.LastFMNetwork(api_key=LASTFM_KEY, api_secret=LASTFM_SECRET)
    for artist in artists:
        try:
            lastfm_artist = network.get_artist(artist)
            artist_info = {
                #'_id': ObjectId(str(artist.get_mbid())),
                'name': lastfm_artist.get_name(),
                'about': lastfm_artist.get_bio_content(),
                'albums': [{
                    'name': album.item.get_name(),
                    'release_date': album.item.get_release_date(),
                    'cover_image': album.item.get_cover_image()
                    } for album in lastfm_artist.get_top_albums()],
                'tags': get_artist_tags(artist)
            }
            artist_list.append(artist_info)
        except:
            pass
    return artist_list


if __name__ == '__main__':
    artist_names = json.loads(sys.argv[1])
    print get_artists(artist_names)
