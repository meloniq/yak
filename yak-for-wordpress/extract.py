#! /usr/bin/python

import os
import re

pat1 = re.compile(r'_(?:_|e|ye)\((?:\'|")(.*?)(?:\'|")\s*,\s*(?:\'|")yak(?:\'|")\s*')
pat2 = re.compile(r'_(?:_|e|ye)\((?:\'|")(.*?)(?:\'|")\s*,\s*(?:\'|")yak-admin(?:\'|")\s*')

vpat = re.compile(r'Version:\s*(.*)')

s = open('yak-for-wordpress.php').read()
mat = vpat.search(s)
ver = mat.group(1)


def listdir(path):
    rtn = []
    for f in os.listdir(path):
        rtn.append(os.path.join(path, f))
    return rtn


def load_keys(path, pat, resources):
    for f in listdir(path):
        if not f.endswith('.php'):
            continue

        print('processing %s' % f)
        s = open(f).read()
    
        for mat in pat.finditer(s):
            res = mat.group(1)
            resources[res.lower()] = res


def write(fname, name, resources):
    out = open(fname, 'w')

    out.write('''# 
# LANGUAGE (LOCALE) translation for YAK.
# See yak-for-wordpress.php for information and license terms
#
msgid ""
msgstr ""
"Project-Id-Version: %s %s\\n"
"Report-Msgid-Bugs-To: \\n"
"POT-Creation-Date: 2006-02-05 17:21+1300\\n"
"PO-Revision-Date: 2009-10-09 17:21+1300\\n"
"Last-Translator: n/a\\n"
"Language-Team: English\\n"
"MIME-Version: 1.0\\n"
"Content-Type: text/plain; charset=UTF-8\\n"
"Content-Transfer-Encoding: 16bit\\n"
''' % (name, ver))

    resource_keys = list(resources.keys())
    resource_keys.sort()

    for res in resource_keys:
        res = resources[res].replace('"', '\\"').replace("\\'", "'")
        out.write('''
msgid "%s"
msgstr ""
''' % (res))
    
    out.close()

paths = [ '.', 
          '../yak-ext-accrecv',
          '../yak-ext-authorizenet',
          '../yak-ext-google-checkout',
          '../yak-ext-manualcc',
          '../yak-ext-paypal-pro',
          '../yak-ext-salestax',
          '../yak-ext-stripe',
         ]

resources = { }
admin_resources = { }

for path in paths:
    load_keys(path, pat1, resources)
    load_keys(path, pat2, admin_resources)

write('lang/yak-XX.po', 'YAK', resources)
write('lang/yak-admin-XX.po', 'YAK Admin', admin_resources)
