# -*- coding: utf-8 -*-

from settings import LASTFM_KEY

import requests

artists = [
    'Chet Faker',
    'FKA twigs',
    'Albin de la Simone',
    'Zero 7',
    'SZA',
    'Angus and Julia Stone',
    'Uffe',
    'Nicolas Jaar',
    'God Is An Astronaut',
    'Jose Gonzalez',
    'Of Monsters and Men',
    'Portishead',
    'PJ Harvey',
    'St. Vincent',
    'Thievery Corporation',
    'Jessie Ware',
    'Clean Bandit',
    'Elliphant',
    'Moby',
    'Alt-J',
    'CHVRCHES',
    'Diplo',
    'The Weeknd',
    'HAIM',
    'LCAW',
    'Blood Orange',
    'Emiliana Torrini',
    'MS MR',
    'Rhye',
    'BANKS',
    'London Grammar',
    'Blur',
    'Grimes',
    'Yelle',
    'Tame Impala',
    'La Roux',
    'Vampire Weekend',
    'MGMT',
    'MIA',
    'Sleigh Bells',
    'The Naked And Famous',
    'Ladytron',
    'Passion Pit',
    'Crystal Fighters',
    'Yeah Yeah Yeahs',
    'James Blake',
    'Empire of the Sun',
    'The Knife',
    'Lykke Li',
    'Bat For Lashes',
    'Crystal Castles',
    'Atari Teenage Riot',
    'TR/ST',
    'Yann Tiersen',
    'The Presets',
    'The xx',
    'Foals',
    'KAKKMADDAFAKKA',
    'Hot Chip',
    'Beach House',
    'Gorillaz',
    'METRONOMY',
    'Cat Power',
]


def main():
    import pprint
    pp = pprint.PrettyPrinter(indent=4)
    tag_list = {}
    for artist_name in artists:
        r = requests.get(
            'http://ws.audioscrobbler.com/2.0/?method=artist.getinfo&artist=%s&api_key=%s&format=json' % (artist_name, LASTFM_KEY))
        tags = r.json()['artist']['tags']['tag']
        try:
            for tag in tags:
                if artist_name in tag_list:
                    tag_list[artist_name].append(tag['name'])
                else:
                    tag_list[artist_name] = [tag['name']]
        except TypeError:
            pass
    pp.pprint(tag_list)

if __name__ == '__main__':
    main()
