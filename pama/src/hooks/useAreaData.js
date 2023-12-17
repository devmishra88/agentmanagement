import { useQuery, useMutation, useQueryClient } from "react-query";
import { secureRequest, objectToFormData } from "../utils/axios-utils";

const addEditArea = (areadata) => {
  return secureRequest({ url: `/area.php`, method: `POST`, data: areadata });
};

const fetchAreas = () => {
  const reqObject = { Mode: `GetAllArea` };
  const formData = objectToFormData(reqObject);
  return secureRequest({ url: `/area.php`, method: `POST`, data: formData });
};

const fetchSingleArea = ({ queryKey }) => {
  const areaId = queryKey[1];
  const reqObject = { Mode: `GetAreaDetail`, recordid: areaId };
  const formData = objectToFormData(reqObject);
  return secureRequest({ url: `/area.php`, method: `POST`, data: formData });
};

export const useAddAreaData = () => {
  const queryClient = useQueryClient();

  return useMutation(addEditArea, {
    // onSuccess:(data)=>{
    //   // queryClient.invalidateQueries(`areas`)
    //   queryClient.setQueriesData(`areas`,(oldQueryData)=>{
    //     return{
    //       ...oldQueryData,
    //       data:[...oldQueryData.data, data.data]
    //     }
    //   })
    // }
    /*---------------suppose data saving will not have any error------------*/
    /*---------------Optimistic Updates------------*/
    onMutate: async (newArea) => {
      await queryClient.cancelQueries(`areas`);
      const previousAreaData = queryClient.getQueryData(`areas`);
      /*----------append new data-------*/
      queryClient.setQueriesData(`areas`, (oldQueryData) => {
        return {
          ...oldQueryData,
          data: [
            ...oldQueryData.data,
            { id: oldQueryData?.data?.length + 1, ...newArea },
          ],
        };
      });
      /*----------return previous catched data-------*/
      return {
        previousAreaData,
      };
    },
    onError: (_error, _area, context) => {
      /*----------set previous data if any error from api-------*/
      queryClient.setQueryData(`areas`, context.previousAreaData);
    },
    onSettled: () => {
      /*----------sync data from the db-------*/
      queryClient.invalidateQueries(`areas`);
    },
  });
};

export const useAreasData = (onSuccess, onError) => {
  return useQuery(`areas`, fetchAreas, {
    onSuccess,
    onError,
  });
};

export const useSingleAreaDataCachedOrg = (areaId) => {
  const queryClient = useQueryClient();

  return useQuery(["area", areaId], fetchSingleArea, {
    initialData: () => {
      const singlearea = queryClient
        .getQueryData(`area`)
        ?.singlearea?.find((area) => parseInt(area.id) === parseInt(areaId));

      if (singlearea) {
        return {
          data: singlearea,
        };
      } else {
        return undefined;
      }
    },
  });
};

export const useSingleAreaData = (areaId) => {
  const queryClient = useQueryClient();

  return useQuery(["area", areaId], fetchSingleArea, {
    initialData: async () => {
      // Fetch the latest data from the API
      await queryClient.prefetchQuery(["area", areaId], fetchSingleArea);

      const singlearea = queryClient
        .getQueryData(["area", areaId])
        ?.singlearea?.find((area) => parseInt(area.id) === parseInt(areaId));

      if (singlearea) {
        return {
          data: singlearea,
        };
      } else {
        return undefined;
      }
    },
  });
};
