#
#  Boxee Service
#  A Python module for interacting the Boxee Application Service.
#
#  Written by /shawn, 13 April 2011
#
#  Usage:
#  A Boxee "service" is used for partners to link user accounts with Boxee user accounts. Normaly if a partner has the
#  ability to use OAuth, users can be linked to accounts from the Boxee website (boxee.tv/services). However, when no
#  OAuth linking is available this can still be accomplished by creating a "hidden service".
#
#  A hidden service is not visible on the Boxee website and therefore accounts must be linked using the Service API
#  from within the partner's application. This is that API. Use this module to link and unlink user accounts and
#  retrieve stored user keys.
#
#  Examples:
#
#  Instantiate object:
#  myService = BoxeeService.BoxeeService()
#
#  Instantiate object but turn logging off:
#  myService = BoxeeService.BoxeeService(False)
#
#  Get service credentials for current Boxee user:
#  myService.getServiceCreds()
#
#  Link and store service credentials for current Boxee user:
#  keys = {'username': 'boxee', 'password': 'boxee'}
#  myService.setServiceCreds(keys)
#
#  Unlink and delete service credentials for the current Boxee user:
#  myService.delServiceCreds()
#
#  Typical response my look like this:
#  result = myService.getServiceCreds()
#  print result
#     { 'result': True, 'response': 200, 'message': 'Successfull',
#       'serviceEmpty': False, 'data': { 'user': 'boxee', 'pass':'boxee' } }
#

import mc
from xml.dom import minidom

class BoxeeService:

   def __init__(self, printToLog=True):
      self.printToLog = printToLog
      self.appid = mc.GetApp().GetId()

   def log(self, s):
      if self.printToLog:
         message = '@BoxeeService: %s' % str(s)
         mc.LogInfo(message)

   def getServiceCreds(self):
      """
      getStoredCreds will query the Boxee app api service to see if the current Boxee
      user is authenticated with the application service (previously created by Boxee devs)
      You do not have to specify the current user, the mc.Http() api will add the
      current user to the request header.
      """

      http = mc.Http()
      data = http.Get('http://app.boxee.tv/api/get_application_data?id=%s' % self.appid)

      result = {
         'result': True,
         'response': http.GetHttpResponseCode(),
         'data': {},
         'serviceEmpty': True,
         'message': ''
         }

      http.Reset()
      if result['response'] == 200:
         # request was successfull
         dom = minidom.parseString(data)
         keyList = dom.getElementsByTagName('credentials')

         try:
            for i in keyList[0].childNodes:
               if i.firstChild.data:
                  result['serviceEmpty'] = False
               result['data'][str(i.nodeName)] = str(i.firstChild.data)
         except:
            self.log('unable to parse xml, there is most likely nothing to return.')

         result['result'] = True

         if result['serviceEmpty']:
            result['message'] = 'The application server responded however there is no data stored for this user.'
         else:
            result['message'] = 'The application server responded and stored values where returned for this user.'

         return result

      else:
         # request failed
         result['result'] = False
         self.log('error while attempting to reach the boxee application service')
         return result


   def setServiceCreds(self, keys={}):
      """
      setServiceCreds will link the current Boxee user to your application service
      and store the keys passed. This data is secure and stored on our servers.
      When request is made from within a Boxee application using the mc.Http api,
      the current logged in Boxee user will be added to the http request header.
      Anything stored here must be predefined and the service needs to be set up
      by a Boxee dev.

      Keyword arguments:
      keys -- dict, holds key=value pairs to be stored

      """

      data = '<app id="%s"><credentials action="set"></credentials></app>' % self.appid
      dom = minidom.parseString(data)

      for key in keys:
         elmt = dom.createElement(key)
         txtn = dom.createTextNode(keys[key])
         elmt.appendChild(txtn)
         dom.getElementsByTagName('credentials')[0].appendChild(elmt)

      data = str(dom.toxml()).replace('<?xml version="1.0" ?>', '')

      http = mc.Http()
      http.SetHttpHeader('Content-Type', 'text/xml')
      set_data = http.Post('http://app.boxee.tv/api/set_application_data', data)

      result = {
         'result': True,
         'response': http.GetHttpResponseCode(),
         'data': {},
         'message': ''
         }

      http.Reset()
      if result['response'] == 200:
         # data successfully stored
         self.log('user linked to application service successfully')
         result['message'] = 'User successfully linked to application service. All key=value pairs stored.'
         return result

      self.log('unable to link user to application service')
      result['result'] = False
      result['message'] = 'There was a problem linking the current user.'
      return result


   def delServiceCreds(self):
      """
      Unlinks the current Boxee user and deletes any stored credentials from the
      applcation service Use this if previously stored credentials are no longer
      valid for any reason.
      """

      self.log('unlinking user from headweb service')
      data = '<app id="%s"><credentials action="del"></credentials></app>' % self.appid

      http = mc.Http()
      http.SetHttpHeader('Content-Type', 'text/xml')
      set_data = http.Post('http://app.boxee.tv/api/set_application_data', data)

      result = {
         'result': True,
         'response': http.GetHttpResponseCode(),
         'data': {},
         'message': ''
         }

      http.Reset()
      if result['response'] == 200:
         # data successfully removed
         self.log('successfully unlinked user from the application service')
         result['message'] = 'User successfully removed from the application service. All key=value pairs deleted.'
         return result

      self.log('error occured while unliking user')
      result['result'] = False
      result['message'] = 'There was a problem unlinking the current user.'
      return result
