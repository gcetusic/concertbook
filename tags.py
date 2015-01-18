# -*- coding: utf-8 -*-

API_URL = 'http://ws.audioscrobbler.com/2.0/'

from settings import LASTFM_KEY

import requests
import json
import sys


def main(artists):
    tag_list = {}
    for artist_name in artists:
        r = requests.get(
            API_URL +
            '?method=artist.getinfo&artist=%s&api_key=%s&format=json' % (
                artist_name, LASTFM_KEY))
        try:
            tags = r.json()['artist']['tags']['tag']
            for tag in tags:
                if artist_name in tag_list:
                    tag_list[artist_name].append(tag['name'])
                else:
                    tag_list[artist_name] = [tag['name']]
        except TypeError:
            pass
    return json.dumps(tag_list)

if __name__ == '__main__':
    print main(json.loads(sys.argv[1]))
